# Helpdesk

Aplikasi helpdesk Laravel 13 + Filament, PHP 8.4, MySQL 8, queue worker, dan Reverb.

## Deployment

Panduan deployment lengkap ada di **[DEPLOYMENT.md](DEPLOYMENT.md)**. Compose utama membangun image aplikasi dan Nginx yang berisi kode serta aset, bukan membaca source dari bind mount saat melayani request.

Ringkasnya:

1. Siapkan `.env` dan `src/.env` dari file `.env.example`; isi secret unik dan URL/domain.
2. Pulihkan schema dasar database ke `helpdesk_db`. Dump tidak disertakan karena dapat berisi data sensitif, dan migration saat ini belum membuat seluruh schema dasar dari database kosong.
3. Build aset frontend, lalu build image dan jalankan Compose sesuai instruksi di [DEPLOYMENT.md](DEPLOYMENT.md).

**Jangan commit `.env`, SQL dump, backup, atau data upload.** Untuk workaround Podman WSL khusus, lihat `compose.podman-wsl.yml`; file itu bukan konfigurasi deployment server.
