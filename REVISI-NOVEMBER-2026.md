# Finora — Revisi Keuangan

- Seeder kini hanya membuat akun awal; tidak membuat buku, saldo awal, kategori, atau transaksi contoh.
- Akun boleh memiliki 0 buku; buku dibuat atas pilihan pengguna.
- Pada form transaksi, kategori dapat diketik bebas dan akan disimpan pada buku aktif serta jenis transaksi terkait.
- Nominal Rupiah otomatis diformat dengan titik saat diketik, disimpan sebagai angka integer ke kolom DECIMAL.
- Reset **tidak otomatis dijalankan** saat instalasi/migration: data nyata hanya dihapus setelah backup dan dua konfirmasi.

## Reset seluruh transaksi lama dan saldo awal

Jalankan pada mesin Debian yang memiliki MariaDB dan database asli:

```bash
cd ~/Projects/Finora
bash scripts/backup-and-reset.sh
```

Skrip meminta password root MariaDB, menyimpan backup SQL privat, mencoba mengarsipkan lampiran privat, lalu meminta konfirmasi eksplisit sebelum menghapus semua transaksi dan mengubah seluruh saldo awal buku menjadi nol. Simpan juga salinan backup SQL dan lampiran di luar mesin sebelum konfirmasi. Tidak menghapus buku, kategori, pengguna atau keanggotaan. **Reset tidak dapat dibatalkan kecuali dengan restore backup.**

## Instalasi

```bash
composer install
npm install
npm run build
php artisan migrate --seed
php artisan serve
```

Gunakan database MariaDB `KY-Akuntasi0` di `.env`. Jangan jalankan `migrate:fresh` pada database yang memiliki data penting.

## Status verifikasi

PHP syntax check: lolos pada file PHP aplikasi. Automated tests belum berhasil dijalankan pada lingkungan paket ini; jalankan `php artisan test` di Debian lokal setelah dependencies dan konfigurasi tersedia. Fitur lain di luar empat revisi ini masih mengikuti status pengembangan sebelumnya.
