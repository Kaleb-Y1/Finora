# Finora — Revisi login Google dan akun demo

## Perubahan
- Seeder tidak lagi membuat admin/budi/opasantoso atau transaksi.
- Tombol demo login dihapus.
- Login Google OAuth via Laravel Socialite. Role akun Google baru: `umum`, tanpa buku awal.
- Akun lama dengan email yang sama tidak ditautkan otomatis.
- Penghapusan akun demo lama TIDAK otomatis (untuk mencegah hilangnya data, buku, transaksi, dan Admin terakhir).

## Instalasi pada Debian 13
1. Backup proyek dan database sebelum menimpa file.
2. Salin source revisi ke proyek, pertahankan `.env`, `.git`, `storage`, dan `vendor`.
3. Jalankan `composer require laravel/socialite` agar composer.json DAN composer.lock diperbarui bersama.
4. Jalankan `php artisan migrate` (JANGAN `migrate:fresh`).
5. Di Google Cloud Console buat project, konfigurasi OAuth consent screen, buat OAuth Client ID tipe **Web application**.
6. Authorized redirect URI: `http://127.0.0.1:8000/auth/google/callback`. Jika aplikasi Testing, masukkan akun sebagai test user.
7. Tambahkan `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI` ke `.env` (jangan commit `.env`).
8. `php artisan config:clear && php artisan route:list --path=auth/google && php artisan serve`.
9. Buka `http://127.0.0.1:8000/login` dan coba Google OAuth.

## Admin pertama
Akun Google baru selalu `umum`. Setelah login pertama, administrator database yang berwenang dapat menetapkan role admin untuk akun terverifikasi miliknya melalui Tinker:
`php artisan tinker`
`$u = App\Models\User::where('email', 'EMAIL_GOOGLE_MILIK_ANDA')->firstOrFail();`
`$u->update(['role' => 'admin']);`
Jalankan hanya untuk akun yang benar-benar Anda kendalikan.

## Akun demo lama
Untuk meninjau akun demo tanpa menghapus data: `SELECT id,username,email,role,status FROM users WHERE username IN ('admin','budi','opasantoso');`
Periksa relasi buku/transaksi dan backup terlebih dahulu. Jangan hapus Admin aktif terakhir. Akun demo yang tidak diperlukan dapat dinonaktifkan oleh admin setelah ada admin pengganti.

## Pengujian
Jalankan `php artisan test` dan uji callback Google di browser dengan kredensial Google yang sah. Integrasi belum dapat diuji end-to-end tanpa OAuth Client ID dan Client Secret milik Anda.
