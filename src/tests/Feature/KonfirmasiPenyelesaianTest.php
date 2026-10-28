<?php

namespace Tests\Feature;

use App\Models\Modules\Master\Barang\Models\MasterBarang;
use App\Models\Modules\Master\Barang\Models\MasterKategoriBarang;
use App\Models\Modules\Master\Ruangan\Models\MasterRuangan;
use App\Models\Modules\Pengajuan\Models\PengajuanBarang;
use App\Models\Modules\Perbaikan\Models\TiketPerbaikan;
use App\Models\User;
use App\Notifications\HelpdeskNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class KonfirmasiPenyelesaianTest extends TestCase
{
    private User $pemohon;

    private User $teknisi;

    private User $admin;

    private MasterRuangan $ruangan;

    private MasterBarang $barang;

    private string $waktuMulaiUji;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'helpdesk_dev',
            'queue.default' => 'sync',
            'helpdesk.notifications.broadcast_enabled' => false,
        ]);

        $this->waktuMulaiUji = now()->subSecond()->format('Y-m-d H:i:s');

        $this->hapusRekamanUji(now()->subDay()->format('Y-m-d H:i:s'));

        Role::findOrCreate('pemohon', 'web');
        Role::findOrCreate('teknisi', 'web');
        Role::findOrCreate('admin', 'web');

        $this->pemohon = $this->buatUser('Pemohon Konfirmasi', 'pemohon.konfirmasi@example.com', 'pemohon');
        $this->teknisi = $this->buatUser('Teknisi Konfirmasi', 'teknisi.konfirmasi@example.com', 'teknisi');
        $this->admin = $this->buatUser('Admin Konfirmasi', 'admin.konfirmasi@example.com', 'admin');

        $this->ruangan = new MasterRuangan;
        $this->ruangan->nama_ruangan = 'RUANG UJI KONFIRMASI';
        $this->ruangan->nama_gedung = 'GEDUNG UJI';
        $this->ruangan->save();

        $kategori = new MasterKategoriBarang;
        $kategori->nama_kategori = 'KATEGORI UJI KONFIRMASI';
        $kategori->deskripsi = 'Kategori untuk pengujian konfirmasi';
        $kategori->is_active = true;
        $kategori->save();

        $this->barang = new MasterBarang;
        $this->barang->kategori_barang_id = $kategori->id;
        $this->barang->nama_barang = 'MASTER BARANG UJI KONFIRMASI';
        $this->barang->is_active = true;
        $this->barang->save();
    }

    protected function tearDown(): void
    {
        $this->hapusRekamanUji($this->waktuMulaiUji);

        parent::tearDown();
    }

    /**
     * Membersihkan seluruh rekaman uji (dipakai di awal dan akhir test agar
     * run sebelumnya yang gagal tidak membuat run berikutnya bentrok).
     *
     * Notifikasi hanya disentuh bila dibuat dalam rentang waktu yang
     * ditentukan, agar notifikasi historis milik record asli tidak pernah
     * ikut terhapus.
     */
    private function hapusRekamanUji(?string $sejak = null): void
    {
        $userIds = DB::table('users')
            ->whereIn('email', [
                'pemohon.konfirmasi@example.com',
                'teknisi.konfirmasi@example.com',
                'admin.konfirmasi@example.com',
            ])
            ->pluck('id');

        $tiketIds = DB::table('tiket_perbaikan')
            ->where('keluhan', 'Tiket uji alur konfirmasi')
            ->pluck('id');

        $barangIds = DB::table('master_barang')
            ->where('nama_barang', 'MASTER BARANG UJI KONFIRMASI')
            ->pluck('id');

        $pengajuanIds = DB::table('pengajuan_barang')
            ->whereIn('barang_id', $barangIds)
            ->orWhere('nama_barang', 'Barang Uji Konfirmasi')
            ->pluck('id');

        $notifikasi = DB::table('notifications');

        if ($sejak !== null) {
            $notifikasi->where('created_at', '>=', $sejak);
        }

        foreach ([...$tiketIds, ...$pengajuanIds] as $referenceId) {
            // Hanya notifikasi yang menunjuk record uji; jangan pernah
            // menyentuh notifikasi historis milik record asli.
            (clone $notifikasi)
                ->where('data->reference_id', (string) $referenceId)
                ->delete();
        }

        DB::table('log_data_tiket_perbaikan')->whereIn('tiket_id', $tiketIds)->delete();
        DB::table('log_data_pengajuan_barang')->whereIn('pengajuan_id', $pengajuanIds)->delete();

        DB::table('tiket_perbaikan')->whereIn('id', $tiketIds)->delete();
        DB::table('pengajuan_barang')->whereIn('id', $pengajuanIds)->delete();

        DB::table('master_ruangan')->where('nama_ruangan', 'RUANG UJI KONFIRMASI')->delete();

        $kategoriIds = DB::table('master_kategori_barang')
            ->where('nama_kategori', 'KATEGORI UJI KONFIRMASI')
            ->pluck('id');

        DB::table('master_barang')
            ->where('nama_barang', 'MASTER BARANG UJI KONFIRMASI')
            ->delete();

        DB::table('master_kategori_barang')->whereIn('id', $kategoriIds)->delete();

        DB::table('model_has_roles')->whereIn('model_id', $userIds)->delete();
        DB::table('users')->whereIn('id', $userIds)->delete();
    }

    public function test_payload_notifikasi_database_mengandung_format_dan_body_filament(): void
    {
        $notification = new HelpdeskNotification(
            type: 'perbaikan.status',
            title: 'Judul Notifikasi',
            message: 'Isi pesan notifikasi.',
            kode: 'TK-2026-001',
            url: '/admin/tiket-perbaikans/1',
            icon: 'heroicon-o-bell',
            color: 'warning',
            referenceId: '1',
            data: ['sender_name' => 'Budi Santoso'],
        );

        $payload = $notification->toDatabase($this->pemohon);

        $this->assertSame('filament', $payload['format']);
        $this->assertSame('Judul Notifikasi', $payload['title']);
        $this->assertSame('Isi pesan notifikasi.', $payload['body']);
        $this->assertSame('warning', $payload['color']);
        $this->assertSame('perbaikan.status', $payload['type']);
        $this->assertSame('TK-2026-001', $payload['kode']);
        $this->assertSame('Budi Santoso', $payload['data']['sender_name']);
        $this->assertNotEmpty($payload['duration']);
        $this->assertNotEmpty($payload['actions']);
    }

    public function test_canal_broadcast_hanya_aktif_bila_konfigurasi_mengizinkan(): void
    {
        $notification = new HelpdeskNotification('perbaikan.status', 'Judul', 'Pesan');

        config(['helpdesk.notifications.broadcast_enabled' => false]);
        $this->assertNotContains('broadcast', $notification->via($this->pemohon));

        config(['helpdesk.notifications.broadcast_enabled' => true]);
        $this->assertContains('broadcast', $notification->via($this->pemohon));
    }

    public function test_staff_meminta_konfirmasi_mengirim_notifikasi_ke_pemohon(): void
    {
        $tiket = $this->buatTiket('In Progress');

        $this->actingAs($this->teknisi);
        Notification::fake();

        $this->assertTrue($tiket->mintaKonfirmasiSelesai('Pekerjaan sudah selesai dikerjakan'));

        $tiket->refresh();

        $this->assertSame('In Progress', $tiket->status);
        $this->assertTrue($tiket->isMenungguKonfirmasi());
        $this->assertNotNull($tiket->konfirmasi_at);
        $this->assertTrue($tiket->logs()->where('kategori_log', 'Konfirmasi')->exists());

        Notification::assertSentTo(
            $this->pemohon,
            fn (HelpdeskNotification $notification): bool => $notification->type === 'perbaikan.confirmation_requested'
        );

        Notification::assertNotSentTo(
            $this->teknisi,
            fn (HelpdeskNotification $notification): bool => $notification->type === 'perbaikan.confirmation_requested'
        );
    }

    public function test_pemohon_mengonfirmasi_menutup_tiket_dan_memberitahu_staf(): void
    {
        $tiket = $this->buatTiket('In Progress');
        $tiket->mintaKonfirmasiSelesai('Silakan dikonfirmasi');

        $this->actingAs($this->pemohon);
        Notification::fake();

        $this->assertTrue($tiket->konfirmasiSelesai('Terima kasih, sudah rapi'));

        $tiket->refresh();

        $this->assertSame('Close', $tiket->status);
        $this->assertTrue($tiket->isKonfirmasiDisetujui());

        Notification::assertSentTo(
            $this->teknisi,
            fn (HelpdeskNotification $notification): bool => $notification->type === 'perbaikan.confirmed'
        );
    }

    public function test_pemohon_menolak_konfirmasi_mengembalikan_tiket_ke_staf(): void
    {
        $tiket = $this->buatTiket('In Progress');
        $tiket->mintaKonfirmasiSelesai('Silakan dikonfirmasi');

        $this->actingAs($this->pemohon);
        Notification::fake();

        $this->assertTrue($tiket->tolakKonfirmasi('Kabel belum dirapikan'));

        $tiket->refresh();

        $this->assertSame('In Progress', $tiket->status);
        $this->assertTrue($tiket->isKonfirmasiDitolak());
        $this->assertFalse($tiket->isMenungguKonfirmasi());

        Notification::assertSentTo(
            $this->teknisi,
            fn (HelpdeskNotification $notification): bool => $notification->type === 'perbaikan.confirmation_rejected'
        );

        $this->actingAs($this->teknisi);

        $this->assertTrue($tiket->mintaKonfirmasiSelesai('Sudah diperbaiki'));
        $this->assertTrue($tiket->fresh()->isMenungguKonfirmasi());
    }

    public function test_penolakan_layanan_mengirim_notifikasi_khusus_ke_pemilik(): void
    {
        $tiket = $this->buatTiket('In Progress');
        $tiket->mintaKonfirmasiSelesai('Menunggu konfirmasi');

        $this->actingAs($this->teknisi);
        Notification::fake();

        $this->assertTrue($tiket->closeAsRejected('Komponen tidak tersedia'));

        $tiket->refresh();

        $this->assertSame('Close', $tiket->status);
        $this->assertNull($tiket->konfirmasi_status);

        Notification::assertSentTo(
            $this->pemohon,
            fn (HelpdeskNotification $notification): bool => $notification->type === 'perbaikan.rejected'
                && $notification->toDatabase($this->pemohon)['format'] === 'filament'
                && filled($notification->toDatabase($this->pemohon)['body'])
        );
    }

    public function test_pembatalan_pengajuan_memberitahu_staf_saja(): void
    {
        $pengajuan = $this->buatPengajuan('Open');

        $this->actingAs($this->pemohon);
        Notification::fake();

        $this->assertTrue($pengajuan->cancelByPemohon('Tidak jadi dipakai'));

        Notification::assertSentTo(
            $this->admin,
            fn (HelpdeskNotification $notification): bool => $notification->type === 'pengajuan.cancelled'
        );

        Notification::assertNotSentTo(
            $this->pemohon,
            fn (HelpdeskNotification $notification): bool => $notification->type === 'pengajuan.cancelled'
        );
    }

    public function test_alur_konfirmasi_pada_pengajuan_barang(): void
    {
        $pengajuan = $this->buatPengajuan('In Progress');

        $this->actingAs($this->admin);
        Notification::fake();

        $this->assertTrue($pengajuan->mintaKonfirmasiSelesai('Barang sudah diserahkan'));
        $this->assertTrue($pengajuan->fresh()->isMenungguKonfirmasi());

        Notification::assertSentTo(
            $this->pemohon,
            fn (HelpdeskNotification $notification): bool => $notification->type === 'pengajuan.confirmation_requested'
        );

        $this->actingAs($this->pemohon);

        $this->assertTrue($pengajuan->konfirmasiSelesai('Barang sudah saya terima'));

        $pengajuan->refresh();

        $this->assertSame('Close', $pengajuan->status);
        $this->assertTrue($pengajuan->isKonfirmasiDisetujui());

        Notification::assertSentTo(
            $this->admin,
            fn (HelpdeskNotification $notification): bool => $notification->type === 'pengajuan.confirmed'
        );
    }

    private function buatUser(string $nama, string $email, string $role): User
    {
        $user = new User;
        $user->name = $nama;
        $user->email = $email;
        $user->password = 'password';
        $user->save();

        $user->assignRole($role);

        return $user;
    }

    private function buatTiket(string $status): TiketPerbaikan
    {
        $tiket = new TiketPerbaikan;
        $tiket->user_id = $this->pemohon->id;
        $tiket->ruangan_id = $this->ruangan->id;
        $tiket->keluhan = 'Tiket uji alur konfirmasi';
        $tiket->deskripsi = 'Deskripsi tiket untuk pengujian konfirmasi penyelesaian';
        $tiket->status = $status;
        $tiket->save();

        return $tiket;
    }

    private function buatPengajuan(string $status): PengajuanBarang
    {
        $pengajuan = new PengajuanBarang;
        $pengajuan->user_id = $this->pemohon->id;
        $pengajuan->barang_id = $this->barang->id;
        $pengajuan->nama_barang = 'Barang Uji Konfirmasi';
        $pengajuan->jumlah = 1;
        $pengajuan->alasan = 'Uji alur konfirmasi penyelesaian';
        $pengajuan->status = $status;
        $pengajuan->save();

        return $pengajuan;
    }
}
