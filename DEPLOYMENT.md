# Deployment Helpdesk

Panduan ini berlaku untuk Docker Compose dan Podman Compose di Linux. Compose production memakai bridge network dan image immutable berisi source serta dependency. Dengan demikian request web tidak membaca source dari bind mount host.

`compose.podman-wsl.yml` tetap disediakan sebagai workaround khusus Podman WSL pada mesin yang bridge network-nya tidak berfungsi. Jangan gunakan file itu untuk Docker atau server Linux.

## Persyaratan dan keamanan

- Docker Compose v2 atau Podman Compose yang mendukung Compose spec yang dipakai.
- PHP source dibangun ke image; perubahan kode membutuhkan rebuild/redeploy image.
- `.env` dan `src/.env` berisi secret dan tidak boleh masuk Git.
- Bind default web hanya ke `127.0.0.1:8082`. Letakkan reverse proxy TLS di depannya atau atur bind address dengan sadar; jangan publikasikan MySQL ke internet.
- Aset Vite memuat konfigurasi publik Reverb dari `src/.env` saat build. Atur domain dan TLS sebelum membangun aset.

## Persiapan environment

```sh
cp .env.example .env
cp src/.env.example src/.env
```

Isi password DB yang sama di kedua file. Samakan `DB_DATABASE=helpdesk_db` dan `DB_USERNAME=helpdesk`. Buat password unik untuk root MySQL dan `APP_KEY` unik. Tetapkan `APP_URL`, `VITE_REVERB_HOST`, `VITE_REVERB_PORT`, serta `VITE_REVERB_SCHEME` sesuai domain publik. Pastikan `APP_ENV=production` dan `APP_DEBUG=false`.

Debugbar merupakan dependency development dan tidak dipasang ke image production (`composer install --no-dev`). Jangan menambahkan Debugbar ke production dependencies.

## Database awal — wajib

Schema dasar aplikasi tidak seluruhnya dibuat oleh migration Laravel. **Pulihkan backup/schema dasar yang telah disetujui ke `helpdesk_db` sebelum menjalankan migration.** SQL dump tidak disertakan di repo dan harus didistribusikan secara terpisah dengan aman. Jangan menjalankan migration pada database kosong; migration awal dapat gagal karena tabel legacy belum ada.

Volume database Compose bernama `helpdesk_mysql_data`. Backup sebelum perubahan schema dan jangan gunakan `docker compose down -v` pada server.

## Build dan jalankan

Jalankan perintah dari root repo. Pastikan file environment sudah siap dan schema dasar telah dipulihkan sebelum langkah migration.

```sh
# Build Vite assets menggunakan src/.env (nilai VITE_* tertanam dalam hasil build).
docker compose --profile tools run --rm assets

# Untuk Docker Compose: build source, dependency Composer production, dan image Nginx.
docker compose build php nginx

# Jalankan database dan tunggu healthcheck.
docker compose up -d db
docker compose ps

# Generate key hanya jika APP_KEY masih kosong; simpan nilai yang ditampilkan
# ke src/.env pada host sebelum menjalankan container permanen.
docker compose run --rm php php artisan key:generate --show

# Setelah schema dasar dipulihkan ke helpdesk_db:
docker compose run --rm php php artisan migrate --force
docker compose up -d
docker compose ps
```

Jika menggunakan Podman Compose 1.6.x, perintah `podman-compose build` dapat mengabaikan properti `build.dockerfile`. Bangun kedua image dengan Podman CLI agar Dockerfile dan root build context eksplisit, lalu jalankan stack dengan Podman Compose:

```sh
podman build -f docker/Dockerfile -t helpdesk-php:latest .
podman build -f docker/nginx/Dockerfile -t helpdesk-nginx:latest .
podman-compose up -d db
podman-compose run --rm php php artisan migrate --force
podman-compose up -d
```

Perintah migration tetap hanya dijalankan setelah schema dasar dipulihkan. Jangan menjalankan Docker Compose dan Podman Compose bersamaan pada project/volume yang sama.

Untuk melihat halaman, gunakan alamat sesuai `APP_BIND_ADDRESS` dan `APP_FORWARD_PORT` (default lokal `http://localhost:8082`). Panel Filament berada di `/admin/login`.

## Tentang performa dan image

- PHP-FPM, queue, dan Reverb menjalankan source serta vendor dari image; tidak ada bind mount `./src` di Compose utama.
- Nginx juga berisi public assets dari image; upload publik saja disimpan pada named volume bersama PHP/queue dan dibaca read-only oleh Nginx.
- OPcache dikonfigurasi untuk source immutable (`opcache.validate_timestamps=0`). Rebuild image setelah perubahan kode; jangan mengandalkan bind mount untuk hot reload pada konfigurasi production.
- Jumlah worker PHP-FPM tetap 20; ukur penggunaan CPU/RAM sebelum mengubahnya. Jangan mengatasi request lambat hanya dengan menaikkan timeout.
- Filesystem server yang dipakai untuk Docker storage sebaiknya filesystem Linux lokal, bukan SMB/NFS/FUSE.

## Operasional

```sh
docker compose logs -f php nginx queue reverb db
docker compose exec php php artisan migrate:status
docker compose down
```

`down` mempertahankan named volumes. Jangan memakai `down -v` kecuali memang bermaksud menghapus volume database dan upload.

## Podman WSL lokal

Pada WSL yang bridge network-nya tidak berfungsi, gunakan workaround lokal:

```sh
# Build image app dengan root context; Podman Compose 1.6.x dapat mengabaikan dockerfile:
podman build -f docker/Dockerfile -t localhost/helpdesk-php:latest .

# Build assets lewat pasta setelah src/.env disiapkan:
podman-compose -f compose.podman-wsl.yml --profile tools run --rm assets

podman-compose -f compose.podman-wsl.yml up -d
```

File ini menggunakan `pasta`, namespace bersama, source bind mount, dan volume cache view khusus. Atur `DB_HOST=127.0.0.1`, `REVERB_HOST=127.0.0.1`, serta nilai `VITE_REVERB_*` untuk browser lokal; build ulang assets setelah perubahan. Workaround ini tidak menggantikan konfigurasi production.
