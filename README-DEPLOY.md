# Helpdesk — Docker, Podman, dan Linux

Compose utama (`docker-compose.yml`) memakai bridge network standar, sehingga cocok untuk Docker Compose dan Podman Compose di Linux. Konfigurasi memakai mode Laravel **production** (`APP_ENV=production`, `APP_DEBUG=false`). `.env` lokal tidak disimpan di Git.

`compose.podman-wsl.yml` adalah workaround khusus Podman pada WSL yang bridge network-nya bermasalah. Jangan pakai file itu untuk Docker atau deployment Linux.
Pada workaround tersebut, cache view Blade memakai named volume terpisah agar `touch()` dapat mengubah timestamp file meski source Laravel berada di bind mount Windows.

## Persiapan

Salin contoh environment dan buat secret unik:

```sh
cp .env.example .env
cp src/.env.example src/.env
```

Isi password yang sama untuk `DB_PASSWORD` di kedua file; samakan juga `DB_DATABASE` dan `DB_USERNAME`. Ubah `APP_URL`, `VITE_REVERB_HOST`, `VITE_REVERB_PORT`, dan `VITE_REVERB_SCHEME` sesuai domain/TLS. Biarkan `APP_DEBUG=false`. Buat `APP_KEY` unik menggunakan `php artisan key:generate` setelah dependency terpasang. Jangan commit atau kirim file `.env`.

Default port web terikat ke localhost (`8082`). Untuk akses publik, gunakan reverse proxy dengan TLS di depannya atau dengan sengaja ubah `APP_BIND_ADDRESS`; jangan mengekspos MySQL ke internet. Reverb diarahkan melalui Nginx pada endpoint `/app/`.

## Database awal — wajib diperhatikan

Migrations aplikasi saat ini mengandalkan schema dasar yang tidak dibuat seluruhnya oleh migration Laravel. **Jangan menjalankan migration pada database kosong.** Pulihkan backup database yang sudah disetujui ke database `helpdesk_db` terlebih dahulu. Dump database tidak disertakan di repo karena dapat mengandung data sensitif; simpan dan distribusikan secara terpisah dengan aman. Setelah schema/data dasar tersedia, jalankan migration yang tertunda.

## Jalankan dengan Docker Compose

```sh
docker compose build php
docker compose up -d db
docker compose run --rm php composer install --no-interaction --prefer-dist
docker compose --profile tools run --rm assets
docker compose run --rm php php artisan key:generate
docker compose run --rm php php artisan migrate --force
docker compose run --rm php php artisan storage:link --force
docker compose up -d
docker compose ps
```

Jalankan `key:generate` hanya jika `APP_KEY` masih kosong. Untuk deployment Linux, pastikan host mengizinkan container menulis ke `src/storage` dan `src/bootstrap/cache`; entrypoint PHP menyiapkan permission direktori itu.

Buka alamat sesuai `APP_URL` dan `APP_FORWARD_PORT`. Panel Filament tersedia di `/admin/login`.

## Podman

Di Linux, gunakan perintah `podman compose` atau `podman-compose` dengan Compose utama yang sama. Pada WSL yang bridge network-nya tidak berfungsi, gunakan file workaround lokal:

```sh
podman-compose -f compose.podman-wsl.yml up -d
```

Untuk workaround WSL, set `DB_HOST=127.0.0.1`, `REVERB_HOST=127.0.0.1`, `VITE_REVERB_HOST=localhost`, `VITE_REVERB_PORT=8081`, dan `VITE_REVERB_SCHEME=http` di `src/.env`; build ulang aset setelahnya. File ini berbagi network namespace dan **bukan** konfigurasi deployment.

## Operasional dan data

```sh
docker compose logs -f php nginx queue reverb db
docker compose exec php php artisan migrate:status
docker compose down
```

`down` mempertahankan named volumes. Hindari `down -v` kecuali memang bermaksud menghapus data database dan dependency. Volume view cache aman dihapus jika perlu, tetapi jangan menghapus volume database. Backup database secara rutin sebelum menjalankan migration.
