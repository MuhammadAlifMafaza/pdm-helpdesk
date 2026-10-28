<?php

namespace Tests\Feature;

use App\Filament\Resources\Master\MasterBarangs\MasterBarangResource;
use App\Models\Modules\Master\Barang\Models\MasterBarang;
use App\Models\Modules\Master\Barang\Models\MasterKategoriBarang;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MasterBarangTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => env('DB_DATABASE', 'helpdesk_dev') === ':memory:' ? 'helpdesk_dev' : env('DB_DATABASE', 'helpdesk_dev'),
        ]);
    }

    public function test_master_barang_uses_correct_table_name(): void
    {
        $barang = new MasterBarang;

        $this->assertSame('master_barang', $barang->getTable());
    }

    public function test_kategori_barang_with_count_barang_executes_successfully(): void
    {
        $kategori = MasterKategoriBarang::create([
            'nama_kategori' => 'Test Kategori',
            'deskripsi' => 'Deskripsi Kategori Test',
            'is_active' => true,
        ]);

        MasterBarang::create([
            'kategori_barang_id' => $kategori->id,
            'nama_barang' => 'Barang Test 1',
            'is_active' => true,
        ]);

        MasterBarang::create([
            'kategori_barang_id' => $kategori->id,
            'nama_barang' => 'Barang Test 2',
            'is_active' => true,
        ]);

        $result = MasterKategoriBarang::query()
            ->withCount('barang')
            ->where('id', $kategori->id)
            ->first();

        $this->assertNotNull($result);
        $this->assertSame(2, $result->barang_count);
        $this->assertSame(2, $result->barang()->count());
    }

    public function test_master_barang_belongs_to_kategori(): void
    {
        $kategori = MasterKategoriBarang::create([
            'nama_kategori' => 'Kategori Relasi',
            'is_active' => true,
        ]);

        $barang = MasterBarang::create([
            'kategori_barang_id' => $kategori->id,
            'nama_barang' => 'Barang Relasi',
            'is_active' => true,
        ]);

        $this->assertInstanceOf(MasterKategoriBarang::class, $barang->kategoriBarang);
        $this->assertSame($kategori->id, $barang->kategoriBarang->id);
    }

    public function test_master_barang_resource_uses_correct_model(): void
    {
        $this->assertSame(MasterBarang::class, MasterBarangResource::getModel());
    }
}
