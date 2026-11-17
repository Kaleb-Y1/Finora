<?php
namespace App\Http\Controllers\Finance;
use App\Http\Controllers\Controller;
use App\Models\{Book,Category,Transaction,TransactionAttachment};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB,Storage};
use Illuminate\Validation\Rule;
class TransactionController extends Controller {
 private function book(Request $request): Book {return $request->user()->activeBooks()->findOrFail(session('active_book_id'));}
 private function transaction(Request $r, Transaction $transaction): Transaction {abort_unless($transaction->book_id===$this->book($r)->id,404);return $transaction;}
 public function index(Request $r) {
  $book=$this->book($r);$q=$book->transactions()->with(['category','creator','attachments'])->latest('transaction_date')->latest('id');
  if(in_array($r->query('type'),['income','expense'],true))$q->where('type',$r->query('type'));
  if($r->filled('search'))$q->where('title','like','%'.addcslashes($r->query('search'),'%_\\').'%');
  if($r->filled('from'))$q->whereDate('transaction_date','>=',$r->query('from'));
  if($r->filled('to'))$q->whereDate('transaction_date','<=',$r->query('to'));
  return view('finance.transactions.index',['items'=>$q->paginate(15)->withQueryString(),'book'=>$book]);
 }
 public function create(Request $r){return view('finance.transactions.form',['item'=>null,'categories'=>$this->book($r)->categories()->where('status','active')->get(),'type'=>in_array($r->query('type'),['income','expense'])?$r->query('type'):'expense']);}
 public function edit(Request $r,Transaction $transaction){$this->transaction($r,$transaction);return view('finance.transactions.form',['item'=>$transaction,'categories'=>$this->book($r)->categories()->where(function($q)use($transaction){$q->where('status','active')->orWhere('id',$transaction->category_id);})->get(),'type'=>$transaction->type]);}
 private function validated(Request $r,Book $book): array {
  return $r->validate(['title'=>'required|string|max:255','type'=>['required',Rule::in(['income','expense'])], 'category_id'=>['required','integer',Rule::exists('categories','id')->where('book_id',$book->id)->where('type',$r->input('type'))], 'amount'=>'required|numeric|gt:0|decimal:0,2|max:9999999999999','transaction_date'=>'required|date|after_or_equal:'.$book->opening_balance_date->format('Y-m-d'),'description'=>'nullable|string|max:3000','attachment'=>'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120']);
 }
 public function store(Request $r){$book=$this->book($r);$data=$this->validated($r,$book);unset($data['attachment']);$key=$r->validate(['request_key'=>'required|uuid'])['request_key'];
  $item=DB::transaction(function()use($r,$book,$data,$key){$existing=$book->transactions()->where('reference_no',$key)->first();if($existing)return $existing;$item=$book->transactions()->create($data+['created_by'=>$r->user()->id,'reference_no'=>$key]);return $item;});
  if($item->wasRecentlyCreated)$this->upload($r,$item);return redirect()->route('transactions.index')->with('success','Transaksi tersimpan.');}
 public function update(Request $r,Transaction $transaction){$this->transaction($r,$transaction);$data=$this->validated($r,$this->book($r));unset($data['attachment']);$transaction->update($data);$this->upload($r,$transaction);return redirect()->route('transactions.index')->with('success','Transaksi diperbarui.');}
 private function upload(Request $r,Transaction $t):void {if(!$r->hasFile('attachment'))return;$f=$r->file('attachment');$path=$f->store('finora/attachments','local');$t->attachments()->create(['file_name'=>$f->getClientOriginalName(),'file_path'=>$path,'mime_type'=>$f->getMimeType(),'file_size'=>$f->getSize(),'uploaded_by'=>$r->user()->id]);}
 public function destroy(Request $r,Transaction $transaction){$this->transaction($r,$transaction);$transaction->delete();return redirect()->route('transactions.index')->with('success','Transaksi dihapus.');}
 public function download(Request $r,TransactionAttachment $attachment){$this->transaction($r,$attachment->transaction);abort_unless(Storage::disk('local')->exists($attachment->file_path),404);return Storage::disk('local')->download($attachment->file_path,$attachment->file_name);}
}
