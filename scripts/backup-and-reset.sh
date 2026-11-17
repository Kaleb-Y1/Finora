#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
mkdir -p storage/app/private/finora-backups
stamp=$(date +%Y%m%d-%H%M%S)
backup="$PWD/storage/app/private/finora-backups/finora-$stamp.sql"
echo 'Backup database KY-Akuntasi0 menggunakan akun root MariaDB.'
mariadb-dump -u root -p --single-transaction --routines --triggers --events --databases 'KY-Akuntasi0' > "$backup"
test -s "$backup"
chmod 600 "$backup"
if [ -d storage/app/private/finora ]; then
  tar -czf "${backup%.sql}-attachments.tar.gz" -C storage/app/private finora
elif [ -d storage/app/finora ]; then
  tar -czf "${backup%.sql}-attachments.tar.gz" -C storage/app finora
fi
echo "Backup dibuat: $backup"
echo 'Simpan salinan backup di tempat aman sebelum reset.'
php artisan finora:reset-finance --backup="$backup"
