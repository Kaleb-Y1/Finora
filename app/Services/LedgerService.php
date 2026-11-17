<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\AuditLog;
use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LedgerService
{
    /**
     * Hitung Saldo Akhir Kas secara akurat:
     * Saldo Akhir = Saldo Awal + Total Pemasukan - Total Pengeluaran
     */
    public function calculateBalance(Book $book): float
    {
        $openingBalance = (float) $book->opening_balance;
        $incomeTotal = (float) $book->transactions()->income()->sum('amount');
        $expenseTotal = (float) $book->transactions()->expense()->sum('amount');

        return $openingBalance + $incomeTotal - $expenseTotal;
    }

    /**
     * Hitung total pemasukan pada periode tertentu.
     */
    public function getIncomeTotal(Book $book, ?string $startDate = null, ?string $endDate = null): float
    {
        $query = $book->transactions()->income();
        if ($startDate && $endDate) {
            $query->whereBetween('transaction_date', [$startDate, $endDate]);
        }

        return (float) $query->sum('amount');
    }

    /**
     * Hitung total pengeluaran pada periode tertentu.
     */
    public function getExpenseTotal(Book $book, ?string $startDate = null, ?string $endDate = null): float
    {
        $query = $book->transactions()->expense();
        if ($startDate && $endDate) {
            $query->whereBetween('transaction_date', [$startDate, $endDate]);
        }

        return (float) $query->sum('amount');
    }

    /**
     * Buat buku keuangan baru beserta inisialisasi kategori bawaan.
     */
    public function createBook(User $owner, array $data): Book
    {
        return DB::transaction(function () use ($owner, $data) {
            $book = Book::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'owner_id' => $owner->id,
                'opening_balance' => $data['opening_balance'] ?? 0.00,
                'opening_balance_date' => $data['opening_balance_date'] ?? now()->toDateString(),
                'status' => 'active',
            ]);

            // Hubungkan owner ke pivot book_user
            $book->members()->attach($owner->id, [
                'role' => 'owner',
                'status' => 'active',
                'joined_at' => now(),
            ]);

            // Kategori bawaan otomatis agar pengguna baru dapat langsung mencatat transaksi
            $defaultCategories = [
                ['name' => 'Gaji & Upah', 'type' => 'income'],
                ['name' => 'Pendapatan Usaha', 'type' => 'income'],
                ['name' => 'Bonus & Hadiah', 'type' => 'income'],
                ['name' => 'Kebutuhan Harian & Dapur', 'type' => 'expense'],
                ['name' => 'Listrik, Air & Internet', 'type' => 'expense'],
                ['name' => 'Transportasi & Bensin', 'type' => 'expense'],
                ['name' => 'Kesehatan & Obat', 'type' => 'expense'],
                ['name' => 'Pendidikan & Kursus', 'type' => 'expense'],
                ['name' => 'Hiburan & Liburan', 'type' => 'expense'],
                ['name' => 'Lain-lain', 'type' => 'expense'],
            ];

            foreach ($defaultCategories as $cat) {
                Category::create([
                    'book_id' => $book->id,
                    'name' => $cat['name'],
                    'type' => $cat['type'],
                    'status' => 'active',
                ]);
            }

            // Catat audit log dan activity log
            AuditLog::create([
                'user_id' => $owner->id,
                'book_id' => $book->id,
                'auditable_type' => Book::class,
                'auditable_id' => $book->id,
                'event' => 'created',
                'new_values' => $book->toArray(),
                'ip_address' => request()->ip(),
            ]);

            ActivityLog::create([
                'user_id' => $owner->id,
                'book_id' => $book->id,
                'action' => 'CREATE_BOOK',
                'module' => 'Multi-Ledger',
                'description' => "Pengguna {$owner->name} membuat buku keuangan baru: {$book->name} dengan saldo awal Rp" . number_format($book->opening_balance, 2, ',', '.'),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return $book;
        });
    }

    /**
     * Transfer kepemilikan buku ke anggota lain.
     */
    public function transferOwnership(Book $book, User $currentOwner, User $newOwner): bool
    {
        return DB::transaction(function () use ($book, $currentOwner, $newOwner) {
            // Pastikan pemilik baru sudah terdaftar sebagai anggota aktif
            $newOwnerPivot = $book->members()->where('users.id', $newOwner->id)->wherePivot('status', 'active')->first();
            if (! $newOwnerPivot) {
                return false;
            }

            $oldValues = ['owner_id' => $book->owner_id];

            // Update pemilik utama di tabel books
            $book->update(['owner_id' => $newOwner->id]);

            // Update role di tabel pivot book_user
            $book->members()->updateExistingPivot($currentOwner->id, ['role' => 'admin']);
            $book->members()->updateExistingPivot($newOwner->id, ['role' => 'owner']);

            AuditLog::create([
                'user_id' => $currentOwner->id,
                'book_id' => $book->id,
                'auditable_type' => Book::class,
                'auditable_id' => $book->id,
                'event' => 'updated',
                'old_values' => $oldValues,
                'new_values' => ['owner_id' => $newOwner->id],
                'ip_address' => request()->ip(),
            ]);

            ActivityLog::create([
                'user_id' => $currentOwner->id,
                'book_id' => $book->id,
                'action' => 'TRANSFER_OWNERSHIP',
                'module' => 'Multi-Ledger',
                'description' => "Kepemilikan buku {$book->name} dialihkan dari {$currentOwner->name} ke {$newOwner->name}.",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return true;
        });
    }
}
