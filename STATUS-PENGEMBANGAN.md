# Finora — Status Pengembangan

Paket ini merupakan **pengembangan parsial** dari ZIP pengguna, **belum versi final**.

## Perubahan yang dibuat
- Implementasi CRUD transaksi berbasis buku aktif, termasuk filter dan pagination.
- Form pemasukan/pengeluaran dengan validasi kategori per buku, nominal, tanggal, dan lampiran privat.
- Idempotency key untuk POST transaksi (menggunakan `reference_no`; lihat catatan penguatan di bawah).
- Download lampiran hanya setelah memverifikasi buku aktif dan keanggotaan.
- Dashboard menampilkan saldo dan transaksi berdasarkan buku aktif.
- Laporan periode dengan saldo awal berjalan, detail transaksi, CSV dan cetak browser.
- Menghapus bypass akses global Admin pada BookPolicy (Admin tidak otomatis dapat melihat buku privat).
- Memperbaiki pembacaan buku aktif pada layout agar melalui relasi keanggotaan aktif.

## Belum selesai / perlu penguatan
- Fitur admin (pengguna, log, backup/restore), profil, dan preferensi aksesibilitas lengkap.
- Ekspor PDF server-side, XLSX asli, PNG dan JPG, grafik Chart.js.
- Pengelolaan lampiran ganti/hapus dan audit trail semua transaksi.
- Idempotency perlu unique constraint `(book_id, reference_no)` serta penanganan race condition; belum aman untuk submit bersamaan.
- Saldo uang masih dikonversi ke float dalam FinanceService dan LedgerService; untuk akurasi nilai besar perlu aritmetika decimal presisi.
- Constraint DB yang memastikan kategori dan transaksi dari buku yang sama masih perlu diperkuat.
- Validasi dan pengujian restore/backup, pengujian aksesibilitas, dan pengujian keamanan menyeluruh.
- Pengujian otomatis belum dapat dijalankan pada lingkungan ini: ekstensi PHP `dom`, `mbstring`, `xmlwriter` tidak tersedia.

## Instalasi Debian 13
```bash
sudo apt update
sudo apt install php8.4-xml php8.4-mbstring php8.4-mysql mariadb-server composer nodejs npm unzip
cd Finora
cp .env.example .env
# Isi DB_CONNECTION=mysql, DB_DATABASE=KY-Akuntasi0 dan kredensial MariaDB di .env
composer install
php artisan key:generate
npm install
npm run build
php artisan migrate --seed
php artisan test
php artisan serve
```

Database MariaDB dengan nama `KY-Akuntasi0` perlu dibuat terlebih dahulu dan user DB perlu diberi hak akses. Jangan menggunakan SQLite untuk pengujian akhir. Jangan mengunggah `.env` asli.
