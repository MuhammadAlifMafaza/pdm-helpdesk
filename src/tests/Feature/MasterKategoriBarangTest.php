<?php

namespace Tests\Feature;

use App\Filament\Resources\Master\MasterKategoriBarang\MasterKategoriBarangResource;
use App\Filament\Resources\Master\MasterKategoriBarang\Pages\ListMasterKategoriBarangs;
use App\Models\Modules\Master\Barang\Models\MasterBarang;
use App\Models\Modules\Master\Barang\Models\MasterKategoriBarang;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class MasterKategoriBarangTest extends TestCase
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

    public function test_master_kategori_barang_resource_uses_correct_model(): void
    {
        $this->assertSame(MasterKategoriBarang::class, MasterKategoriBarangResource::getModel());
    }

    public function test_list_master_kategori_barangs_table_renders_successfully(): void
    {
        MasterKategoriBarang::create([
            'nama_kategori' => 'Kategori Render Test',
            'deskripsi' => 'Deskripsi Kategori',
            'is_active' => true,
        ]);

        Livewire::test(ListMasterKategoriBarangs::class)
            ->assertSuccessful()
            ->assertTableBulkActionHidden('forceDelete')
            ->filterTable('trashed', true)
            ->assertTableBulkActionVisible('forceDelete');
    }

    public function test_force_delete_bulk_action_prevents_deleting_kategori_with_barang(): void
    {
        $kategori = MasterKategoriBarang::create([
            'nama_kategori' => 'Kategori With Barang',
            'is_active' => true,
        ]);

        MasterBarang::create([
            'kategori_barang_id' => $kategori->id,
            'nama_barang' => 'Barang Relasi Kategori',
            'is_active' => true,
        ]);

        $kategori->delete();

        Livewire::test(ListMasterKategoriBarangs::class)
            ->filterTable('trashed', true)
            ->callTableBulkAction('forceDelete', [$kategori]);

        $this->assertDatabaseHas('master_kategori_barang', [
            'id' => $kategori->id,
        ]);
    }

    public function test_force_delete_bulk_action_deletes_kategori_without_barang(): void
    {
        $kategori = MasterKategoriBarang::create([
            'nama_kategori' => 'Kategori Without Barang',
            'is_active' => true,
        ]);

        $kategori->delete();

        Livewire::test(ListMasterKategoriBarangs::class)
            ->filterTable('trashed', true)
            ->callTableBulkAction('forceDelete', [$kategori]);

        $this->assertDatabaseMissing('master_kategori_barang', [
            'id' => $kategori->id,
        ]);
    }
}
