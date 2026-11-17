<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Transaction;
use App\Models\Book;

class ResetFinoraFinance extends Command
{
    protected $signature = 'finora:reset-finance {--backup= : Path to an existing verified database backup}';
    protected $description = 'Reset all financial transactions and opening balances only after backup and confirmation';

    public function handle(): int
    {
        $path = $this->option('backup');
        if (!$path || !is_file($path) || filesize($path) < 100) {
            $this->error('Backup SQL tidak ditemukan atau kosong. Buat backup terlebih dahulu.');
            return self::FAILURE;
        }
        if (config('database.default') !== 'mysql') {
            $this->error('Reset hanya diizinkan untuk koneksi MariaDB/MySQL.');
            return self::FAILURE;
        }
        $count = Transaction::withTrashed()->count();
        $books = Book::withTrashed()->count();
        $this->warn("Akan menghapus permanen {$count} transaksi dan mengubah saldo awal {$books} buku menjadi Rp0.");
        $this->warn('Lampiran transaksi juga akan dihapus; backup database tidak otomatis mencakup file lampiran.');
        if (!$this->confirm('Apakah backup SQL DAN file lampiran sudah disimpan secara aman?', false)) return self::FAILURE;
        if ($this->ask('Ketik RESET FINORA untuk melanjutkan') !== 'RESET FINORA') return self::FAILURE;
        $paths = DB::table('transaction_attachments')->pluck('file_path')->all();
        DB::transaction(function () {
            DB::table('transaction_attachments')->delete();
            DB::table('transactions')->delete();
            DB::table('books')->update(['opening_balance' => 0]);
        });
        foreach ($paths as $file) Storage::disk('local')->delete($file);
        $this->info('Reset berhasil. Buku, pengguna, kategori, dan keanggotaan tetap dipertahankan.');
        return self::SUCCESS;
    }
}
