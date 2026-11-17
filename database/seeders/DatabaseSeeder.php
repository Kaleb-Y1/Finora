<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Book;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with initial accounts and books.
     */
    public function run(): void
    {
        // 1. Akun Admin
        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrator Finora',
                'email' => 'admin@finora.local',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'status' => 'active',
            ]
        );
        UserPreference::firstOrCreate(['user_id' => $admin->id], [
            'font_size' => 'normal',
            'high_contrast' => false,
            'text_to_speech' => false,
        ]);

        // 2. Akun Umum (Budi Pratama)
        $umum = User::firstOrCreate(
            ['username' => 'budi'],
            [
                'name' => 'Budi Pratama',
                'email' => 'budi@finora.local',
                'password' => Hash::make('password123'),
                'role' => 'umum',
                'status' => 'active',
            ]
        );
        UserPreference::firstOrCreate(['user_id' => $umum->id], [
            'font_size' => 'normal',
            'high_contrast' => false,
            'text_to_speech' => false,
        ]);

        // 3. Akun Orang Tua (Opa Santoso)
        $orangTua = User::firstOrCreate(
            ['username' => 'opasantoso'],
            [
                'name' => 'Opa Santoso',
                'email' => 'opa@finora.local',
                'password' => Hash::make('password123'),
                'role' => 'orang_tua',
                'status' => 'active',
            ]
        );
        UserPreference::firstOrCreate(['user_id' => $orangTua->id], [
            'font_size' => 'large',
            'high_contrast' => false,
            'text_to_speech' => true,
        ]);

        // 4. Buku Kas 1: Keuangan Keluarga (Owner: Budi)
        $book1 = Book::firstOrCreate(
            ['name' => 'Keuangan Keluarga', 'owner_id' => $umum->id],
            [
                'description' => 'Buku kas pencatatan pengeluaran dan pemasukan bulanan rumah tangga.',
                'opening_balance' => 10000000.00,
                'opening_balance_date' => '2026-01-01',
                'status' => 'active',
            ]
        );
        $book1->members()->syncWithoutDetaching([
            $umum->id => ['role' => 'owner', 'status' => 'active'],
            $orangTua->id => ['role' => 'member', 'status' => 'active'],
        ]);

        // Kategori untuk Buku 1
        $catGaji1 = Category::firstOrCreate(['book_id' => $book1->id, 'name' => 'Gaji & Pendapatan'], ['type' => 'income']);
        $catBonus1 = Category::firstOrCreate(['book_id' => $book1->id, 'name' => 'Bonus & THR'], ['type' => 'income']);
        $catMakan1 = Category::firstOrCreate(['book_id' => $book1->id, 'name' => 'Kebutuhan Dapur & Makan'], ['type' => 'expense']);
        $catListrik1 = Category::firstOrCreate(['book_id' => $book1->id, 'name' => 'Listrik, Air & Internet'], ['type' => 'expense']);
        $catKesehatan1 = Category::firstOrCreate(['book_id' => $book1->id, 'name' => 'Kesehatan & Obat'], ['type' => 'expense']);

        // Transaksi Sampel Buku 1
        Transaction::firstOrCreate(
            ['book_id' => $book1->id, 'reference_no' => 'TRX-INIT-001'],
            [
                'category_id' => $catGaji1->id,
                'created_by' => $umum->id,
                'type' => 'income',
                'title' => 'Gaji Bulanan Januari',
                'amount' => 7500000.00,
                'transaction_date' => '2026-01-05',
                'description' => 'Transfer gaji bulanan utama',
            ]
        );
        Transaction::firstOrCreate(
            ['book_id' => $book1->id, 'reference_no' => 'TRX-INIT-002'],
            [
                'category_id' => $catListrik1->id,
                'created_by' => $umum->id,
                'type' => 'expense',
                'title' => 'Tagihan Listrik & WiFi',
                'amount' => 850000.00,
                'transaction_date' => '2026-01-10',
                'description' => 'Pembayaran tagihan PLN dan IndiHome',
            ]
        );
        Transaction::firstOrCreate(
            ['book_id' => $book1->id, 'reference_no' => 'TRX-INIT-003'],
            [
                'category_id' => $catMakan1->id,
                'created_by' => $orangTua->id,
                'type' => 'expense',
                'title' => 'Belanja Sayur & Buah Mingguan',
                'amount' => 350000.00,
                'transaction_date' => '2026-01-12',
                'description' => 'Belanja mingguan di pasar tradisional',
            ]
        );

        // 5. Buku Kas 2: Tabungan Pensiun (Owner: Opa Santoso)
        $book2 = Book::firstOrCreate(
            ['name' => 'Tabungan & Dana Pensiun', 'owner_id' => $orangTua->id],
            [
                'description' => 'Dana simpanan dan uang pensiun Opa Santoso.',
                'opening_balance' => 25000000.00,
                'opening_balance_date' => '2026-01-01',
                'status' => 'active',
            ]
        );
        $book2->members()->syncWithoutDetaching([
            $orangTua->id => ['role' => 'owner', 'status' => 'active'],
            $umum->id => ['role' => 'admin', 'status' => 'active'],
        ]);

        $catPensiun2 = Category::firstOrCreate(['book_id' => $book2->id, 'name' => 'Uang Pensiun'], ['type' => 'income']);
        $catKesehatan2 = Category::firstOrCreate(['book_id' => $book2->id, 'name' => 'Medical Check Up & Vitamin'], ['type' => 'expense']);

        Transaction::firstOrCreate(
            ['book_id' => $book2->id, 'reference_no' => 'TRX-INIT-004'],
            [
                'category_id' => $catPensiun2->id,
                'created_by' => $orangTua->id,
                'type' => 'income',
                'title' => 'Penerimaan Dana Pensiun',
                'amount' => 3200000.00,
                'transaction_date' => '2026-01-02',
                'description' => 'Penerimaan dana pensiun bulanan',
            ]
        );
        Transaction::firstOrCreate(
            ['book_id' => $book2->id, 'reference_no' => 'TRX-INIT-005'],
            [
                'category_id' => $catKesehatan2->id,
                'created_by' => $orangTua->id,
                'type' => 'expense',
                'title' => 'Beli Vitamin & Obat Rutin',
                'amount' => 450000.00,
                'transaction_date' => '2026-01-15',
                'description' => 'Vitamin lansia dan obat tensi',
            ]
        );

        // 6. Activity log inisiasi
        ActivityLog::create([
            'user_id' => $admin->id,
            'book_id' => null,
            'action' => 'SYSTEM_INIT',
            'module' => 'Sistem',
            'description' => 'Inisialisasi sistem Finora dan database KY-Akuntasi0 berhasil dilakukan.',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'SystemSeeder',
        ]);
    }
}
