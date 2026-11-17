# Finora — Revisi kategori dan sistem admin

## Perubahan
- Menu dan rute pengelolaan kategori mandiri dihapus untuk semua role. Kategori tetap dipakai secara internal pada form transaksi, dengan nama bebas agar transaksi lama tidak rusak.
- Sistem Admin kini memiliki halaman nyata untuk manajemen pengguna (tambah/ubah/aktif-nonaktif/role), audit trail, log aktivitas, log error, riwayat login, dan backup database privat serta unduhan.
- Perlindungan role admin tetap berlaku di backend, termasuk larangan menonaktifkan admin aktif terakhir.
- Restore otomatis tidak disediakan karena membutuhkan prosedur verifikasi integritas dan pemulihan yang lebih aman.

## Instalasi revisi
1. Backup folder proyek, database, dan lampiran terlebih dahulu.
2. Ekstrak ZIP ke folder baru, salin `.env` lama dengan aman.
3. `composer install && npm install && npm run build`
4. `php artisan config:clear && php artisan migrate`
5. `php artisan route:list --path=admin` dan `php artisan test`
6. Login sebagai admin dan uji menu Sistem Admin. Backup memerlukan `mariadb-dump` di PATH dan akses database.

## Catatan
Tidak ada reset database dalam revisi ini. Jangan jalankan `migrate:fresh`. Pengujian end-to-end MariaDB harus dilakukan di komputer yang memiliki konfigurasi database yang benar.
