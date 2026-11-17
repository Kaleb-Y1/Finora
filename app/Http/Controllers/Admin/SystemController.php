<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{User, AuditLog, ActivityLog, ErrorLog, LoginLog, DatabaseBackup};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Storage};
use Illuminate\Validation\Rule;
use Symfony\Component\Process\Process;

class SystemController extends Controller
{
    public function users(Request $request) {
        $users = User::query()->when($request->filled('q'), fn($q) => $q->where(fn($sub) => $sub->where('name','like','%'.$request->q.'%')->orWhere('username','like','%'.$request->q.'%')))->orderBy('id')->paginate(15)->withQueryString();
        return view('admin.users', compact('users'));
    }
    public function storeUser(Request $request) {
        $data=$request->validate(['name'=>'required|string|max:255','username'=>'required|alpha_dash|max:100|unique:users,username','email'=>'nullable|email|max:255|unique:users,email','password'=>'required|string|min:8','role'=>['required',Rule::in(['admin','umum','orang_tua'])]]);
        $data['status']='active'; $user=User::create($data);
        $this->auditChange('created',$user,[],['name'=>$user->name,'role'=>$user->role,'status'=>$user->status]);
        return back()->with('success','Pengguna berhasil dibuat.');
    }
    public function updateUser(Request $request,User $user) {
        $data=$request->validate(['name'=>'required|string|max:255','username'=>['required','alpha_dash','max:100',Rule::unique('users','username')->ignore($user->id)],'email'=>['nullable','email','max:255',Rule::unique('users','email')->ignore($user->id)],'role'=>['required',Rule::in(['admin','umum','orang_tua'])],'status'=>['required',Rule::in(['active','inactive'])],'password'=>'nullable|string|min:8']);
        if (empty($data['password'])) unset($data['password']);
        DB::transaction(function () use ($user,$data) {
            User::where('role','admin')->where('status','active')->lockForUpdate()->get();
            if ($user->role==='admin' && $user->status==='active' && ($data['role']!=='admin'||$data['status']!=='active') && User::where('role','admin')->where('status','active')->count()<=1) {
                abort(422,'Admin aktif terakhir tidak boleh dinonaktifkan atau diturunkan.');
            }
            if ($user->id===auth()->id() && ($data['role']!=='admin'||$data['status']!=='active')) abort(422,'Tidak boleh menonaktifkan atau menurunkan akun sendiri.');
            $before=$user->only(['name','username','email','role','status']); $user->update($data);
            $this->auditChange('updated',$user,$before,$user->only(['name','username','email','role','status']));
        });
        return back()->with('success','Pengguna berhasil diperbarui.');
    }
    private function auditChange(string $event,User $user,array $old,array $new): void {
        AuditLog::create(['user_id'=>auth()->id(),'auditable_type'=>User::class,'auditable_id'=>$user->id,'event'=>$event,'old_values'=>$old,'new_values'=>$new,'ip_address'=>request()->ip()]);
    }
    public function audit() { return $this->logPage('Audit Trail', AuditLog::with('user')->latest('id')->paginate(20), ['ID','Pengguna','Aksi','Objek','Waktu'], 'audit'); }
    public function activity() { return $this->logPage('Log Aktivitas', ActivityLog::with('user')->latest('id')->paginate(20), ['ID','Pengguna','Aksi','Modul','Waktu'], 'activity'); }
    public function errors() { return $this->logPage('Log Error', ErrorLog::latest('id')->paginate(20), ['ID','Level','Pesan','Lokasi','Waktu'], 'error'); }
    public function logins() { return $this->logPage('Riwayat Login', LoginLog::latest('id')->paginate(20), ['ID','Username','Status','IP','Waktu'], 'login'); }
    private function logPage($title,$records,$headers,$kind) {return view('admin.logs',compact('title','records','headers','kind'));}
    public function backups() { $backups=DatabaseBackup::latest('id')->paginate(20); return view('admin.backups',compact('backups')); }
    public function createBackup(Request $request) {
        $request->validate(['confirmation'=>['required',Rule::in(['BACKUP FINORA'])]]);
        $config=config('database.connections.mysql');
        if (($config['driver']??'')!=='mysql') abort(422,'Backup hanya mendukung MariaDB/MySQL.');
        $filename='finora-'.now()->format('Ymd-His').'-'.bin2hex(random_bytes(4)).'.sql';
        $path='backups/'.$filename;
        $absolute=Storage::disk('local')->path($path);
        if (!is_dir(dirname($absolute))) mkdir(dirname($absolute),0700,true);
        $command=['mariadb-dump','--single-transaction','--routines','--triggers','--host='.$config['host'],'--port='.(string)($config['port']??3306),'--user='.$config['username'],'--result-file='.$absolute,'--',$config['database']];
        $process=new Process($command,null,['MYSQL_PWD'=>(string)($config['password']??'')]);
        $process->setTimeout(180);
        try { $process->run(); } catch (\Throwable $e) { @unlink($absolute); report($e); return back()->with('error','Backup gagal dijalankan. Periksa konfigurasi server.'); }
        if (!$process->isSuccessful() || !is_file($absolute) || filesize($absolute)===0) { @unlink($absolute); return back()->with('error','Backup gagal. Periksa instalasi mariadb-dump dan izin database.'); }
        @chmod($absolute,0600);
        DatabaseBackup::create(['filename'=>$filename,'file_path'=>$path,'file_size'=>filesize($absolute),'created_by'=>auth()->id(),'status'=>'completed']);
        ActivityLog::create(['user_id'=>auth()->id(),'action'=>'backup_created','module'=>'system','description'=>'Backup database dibuat','ip_address'=>$request->ip(),'created_at'=>now()]);
        return back()->with('success','Backup berhasil dibuat.');
    }
    public function downloadBackup(DatabaseBackup $backup) {
        abort_unless($backup->status==='completed' && str_starts_with($backup->file_path,'backups/') && Storage::disk('local')->exists($backup->file_path),404);
        return Storage::disk('local')->download($backup->file_path,$backup->filename);
    }
}
