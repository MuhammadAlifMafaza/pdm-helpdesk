<?php

namespace Tests\Feature;

use App\Models\Modules\Laporan\Models\LaporanKegiatan;
use App\Models\User;
use App\Services\Laporan\LaporanExportService;
use App\Services\Laporan\Pdf\LaporanKegiatanPdf;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LaporanPrintTest extends TestCase
{
    private User $staff;

    private User $pemohon;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'helpdesk_dev',
            'queue.default' => 'sync',
        ]);

        DB::purge('mysql');
        DB::reconnect('mysql');

        foreach (['admin', 'pemohon'] as $role) {
            Role::findOrCreate($role, 'web');
        }

        $this->staff = User::create([
            'name' => 'Admin Uji Cetak',
            'email' => 'admin@uji-cetak.test',
            'password' => bcrypt('password'),
        ]);
        $this->staff->syncRoles(['admin']);

        $this->pemohon = User::create([
            'name' => 'Pemohon Uji Cetak',
            'email' => 'pemohon@uji-cetak.test',
            'password' => bcrypt('password'),
        ]);
        $this->pemohon->syncRoles(['pemohon']);
    }

    protected function tearDown(): void
    {
        $userIds = User::where('email', 'like', '%@uji-cetak.test')->pluck('id');

        DB::table('model_has_roles')
            ->whereIn('model_id', $userIds)
            ->where('model_type', User::class)
            ->delete();

        User::whereIn('id', $userIds)->forceDelete();

        parent::tearDown();
    }

    /**
     * Payload contoh dengan records berbentuk array seperti hasil serialisasi session.
     */
    private function payloadCetak(array $records = []): array
    {
        return [
            'title' => 'LAPORAN KEGIATAN TEKNISI',
            'documentNumber' => 'No. 005/IWIMA/KTPI-P3SDI/0426',
            'documentCode' => '1FM-01.07.16/R0',
            'periode' => '01-10-2026 - 25-10-2026',
            'printedBy' => 'Admin Uji Cetak',
            'printDate' => now()->translatedFormat('d F Y H:i'),
            'records' => $records,
            'signatures' => [
                ['title' => 'Ka. Bidang Teknisi & Perawatan Infrastruktur', 'name' => 'Edi Purwanto, S.Kom'],
                ['title' => 'Ka. UPT Laboratorium Komputer & Bahasa', 'name' => 'Wachid Darmawan, M.Kom'],
            ],
        ];
    }

    public function test_tamu_dialihkan_ke_halaman_login(): void
    {
        $this->get('/laporan/cetak')->assertRedirect('/login');
    }

    public function test_pemohon_dialihkan_ke_halaman_sendiri(): void
    {
        $this->actingAs($this->pemohon)
            ->get('/laporan/cetak')
            ->assertRedirect($this->pemohon->homeUrl());
    }

    public function test_tanpa_payload_dialihkan_ke_halaman_sendiri(): void
    {
        $this->actingAs($this->staff)
            ->get('/laporan/cetak')
            ->assertRedirect($this->staff->homeUrl());
    }

    public function test_payload_valid_merender_halaman_cetak(): void
    {
        $this->actingAs($this->staff)
            ->withSession(['laporan.print' => [
                'view' => 'laporan.print.kegiatan',
                'data' => $this->payloadCetak(),
            ]])
            ->get('/laporan/cetak')
            ->assertOk()
            ->assertSee('LAPORAN KEGIATAN TEKNISI')
            ->assertSee('Tidak ada kegiatan pada periode ini.')
            ->assertSee('window.print', false);
    }

    public function test_payload_array_merender_data_kegiatan(): void
    {
        $data = $this->payloadCetak([
            [
                'tanggal_kegiatan' => '2026-10-25 00:00:00',
                'nama_teknisi' => 'Teknisi Uji',
                'lokasi' => 'Laboratorium',
                'deskripsi' => 'Pemeriksaan perangkat',
            ],
        ]);

        $this->actingAs($this->staff)
            ->withSession(['laporan.print' => [
                'view' => 'laporan.print.kegiatan',
                'data' => $data,
            ]])
            ->get('/laporan/cetak')
            ->assertSee('25/10/2026')
            ->assertSee('Teknisi Uji')
            ->assertSee('Pemeriksaan perangkat');
    }

    public function test_payload_array_merender_data_perbaikan_dengan_relasi_array(): void
    {
        $data = $this->payloadCetak([
            [
                'status_label' => 'Close',
                'nama_pemohon' => 'Pemohon Uji',
                'lokasi' => 'Laboratorium',
                'tiket' => [
                    'kode_tiket' => 'TKT-001',
                    'created_at' => '2026-10-20 00:00:00',
                    'waktu_selesai' => '2026-10-25 00:00:00',
                    'keluhan' => 'Komputer tidak menyala',
                    'status_label' => 'Selesai',
                ],
            ],
        ]);

        $this->actingAs($this->staff)
            ->withSession(['laporan.print' => [
                'view' => 'laporan.print.perbaikan',
                'data' => $data,
            ]])
            ->get('/laporan/cetak')
            ->assertSee('TKT-001')
            ->assertSee('20/10/2026')
            ->assertSee('25/10/2026')
            ->assertSee('Komputer tidak menyala')
            ->assertSee('Selesai');
    }

    public function test_payload_array_merender_data_permintaan_dengan_relasi_array(): void
    {
        $data = $this->payloadCetak([
            [
                'kode_pengajuan' => 'PGB-001',
                'nama_pemohon' => 'Pemohon Uji',
                'outcome_label' => 'Disetujui',
                'pengajuan' => [
                    'nama_barang' => 'Keyboard',
                    'jumlah' => 2,
                    'status_label' => 'Selesai',
                    'waktu_mulai' => '2026-10-24 08:30:00',
                    'waktu_selesai' => '2026-10-25 09:45:00',
                    'durasi_pengerjaan' => '1 hari',
                ],
            ],
        ]);

        $this->actingAs($this->staff)
            ->withSession(['laporan.print' => [
                'view' => 'laporan.print.permintaan',
                'data' => $data,
            ]])
            ->get('/laporan/cetak')
            ->assertSee('PGB-001')
            ->assertSee('Keyboard')
            ->assertSee('24/10/2026 08:30')
            ->assertSee('25/10/2026 09:45')
            ->assertSee('Disetujui');
    }

    public function test_service_print_menyimpan_payload_dan_url_relatif(): void
    {
        $url = app(LaporanExportService::class)->print(
            query: LaporanKegiatan::query()->whereRaw('1 = 0'),
            exportClass: LaporanKegiatanPdf::class,
            periode: 'Periode Uji Cetak',
        );

        // URL relatif: tidak boleh memakai APP_URL absolut.
        $this->assertSame('/laporan/cetak', $url);

        $html = session('laporan.print.html');

        $this->assertIsString($html);
        $this->assertStringContainsString('Periode Uji Cetak', $html);
        $this->assertStringContainsString('Tidak ada kegiatan pada periode ini.', $html);
        $this->assertStringContainsString('size: 297mm 210mm', $html);

        $this->actingAs($this->staff)
            ->withSession(['laporan.print' => ['html' => $html]])
            ->get('/laporan/cetak')
            ->assertOk()
            ->assertSee('Periode Uji Cetak')
            ->assertSee('window.print', false);
    }

    public function test_service_print_merender_accessor_sebelum_payload_masuk_session(): void
    {
        config([
            'database.connections.print_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => false,
            ],
        ]);
        DB::purge('print_test');

        Schema::connection('print_test')->create('view_laporan_kegiatan', function (Blueprint $table): void {
            $table->unsignedBigInteger('id_log');
            $table->string('nama_teknisi');
            $table->string('unit_bidang')->nullable();
            $table->date('tanggal');
            $table->text('deskripsi_kegiatan');
            $table->dateTime('waktu_dibuat')->nullable();
        });
        Schema::connection('print_test')->create('log_harian_teknisi', function (Blueprint $table): void {
            $table->id();
            $table->softDeletes();
        });
        Schema::connection('print_test')->create('master_ruangan', function (Blueprint $table): void {
            $table->id();
            $table->string('nama_ruangan');
        });
        DB::connection('print_test')
            ->table('view_laporan_kegiatan')
            ->insert([
                'id_log' => 987,
                'nama_teknisi' => 'Teknisi Aksesori',
                'unit_bidang' => 'Teknisi',
                'tanggal' => '2026-10-25',
                'deskripsi_kegiatan' => 'Deskripsi dari accessor',
            ]);

        app(LaporanExportService::class)->print(
            query: LaporanKegiatan::on('print_test')
                ->withoutGlobalScopes()
                ->where('id_log', 987),
            exportClass: LaporanKegiatanPdf::class,
            periode: 'Periode Uji',
        );

        $html = session('laporan.print.html');

        $this->assertIsString($html);
        $this->assertStringContainsString('25/10/2026', $html);
        $this->assertStringContainsString('Teknisi Aksesori', $html);
        $this->assertStringContainsString('Deskripsi dari accessor', $html);

        $this->actingAs($this->staff)
            ->withSession(['laporan.print' => ['html' => $html]])
            ->get('/laporan/cetak')
            ->assertOk()
            ->assertSee('25/10/2026')
            ->assertSee('Teknisi Aksesori')
            ->assertSee('Deskripsi dari accessor');
    }
}
