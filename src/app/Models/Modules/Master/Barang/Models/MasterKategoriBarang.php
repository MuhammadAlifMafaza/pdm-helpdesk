<?php

namespace App\Models\Modules\Master\Barang\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterKategoriBarang extends Model
{
    use SoftDeletes;

    protected $table = 'master_kategori_barang';

    protected $fillable = [
        'nama_kategori',
        'deskripsi',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relasi kategori ke Master Barang.
     *
     * Satu kategori dapat memiliki banyak barang.
     */
    public function barang(): HasMany
    {
        return $this->hasMany(
            MasterBarang::class,
            'kategori_barang_id'
        );
    }

    /**
     * Scope kategori aktif.
     *
     * Digunakan ketika kategori diperlukan
     * untuk transaksi/data baru.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope kategori tidak aktif.
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    /**
     * Menentukan apakah kategori aktif.
     */
    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    /**
     * Menentukan apakah kategori memiliki Master Barang.
     */
    public function hasBarang(): bool
    {
        return $this->barang()->exists();
    }

    /**
     * Mengambil jumlah Master Barang
     * yang berada pada kategori ini.
     */
    public function getJumlahBarangAttribute(): int
    {
        return $this->barang()->count();
    }

    /**
     * Menentukan apakah kategori dapat digunakan
     * untuk data Master Barang baru.
     */
    public function canBeUsedForNewBarang(): bool
    {
        return $this->deleted_at === null
            && $this->is_active === true;
    }

    /**
     * Menentukan apakah kategori aman
     * untuk physical delete.
     *
     * Kategori tidak boleh dihapus permanen
     * apabila masih memiliki relasi barang.
     */
    public function canBeForceDeleted(): bool
    {
        return !$this->barang()
            ->withTrashed()
            ->exists();
    }

    /**
     * Proteksi physical delete.
     */
    protected static function booted(): void
    {
        static::forceDeleting(
            function (MasterKategoriBarang $kategori): void {
                if (!$kategori->canBeForceDeleted()) {
                    throw new \RuntimeException(
                        'Kategori barang tidak dapat dihapus secara permanen '
                        . 'karena masih memiliki relasi barang.'
                    );
                }
            }
        );
    }
}