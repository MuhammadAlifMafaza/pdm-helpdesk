<?php

namespace App\Models\Modules\Master\Barang\Models;

use App\Models\Modules\Pengajuan\Models\PengajuanBarang;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'kategori_barang_id',
    'nama_barang',
    'keterangan',
    'is_active',
])]
class MasterBarang extends Model
{
    use SoftDeletes;

    protected $table = 'master_barang';

    protected $fillable = [
        'kategori_barang_id',
        'nama_barang',
        'keterangan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relasi ke Master Kategori Barang.
     */
    public function kategoriBarang(): BelongsTo
    {
        return $this->belongsTo(
            MasterKategoriBarang::class,
            'kategori_barang_id'
        );
    }

    /**
     * Alias relasi kategori.
     */
    public function kategori(): BelongsTo
    {
        return $this->kategoriBarang();
    }

    /**
     * Relasi ke Pengajuan Barang.
     *
     * Satu Master Barang dapat digunakan
     * pada banyak transaksi pengajuan.
     */
    public function pengajuanBarang(): HasMany
    {
        return $this->hasMany(
            PengajuanBarang::class,
            'barang_id'
        );
    }

    /**
     * Scope barang aktif.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope barang tidak aktif.
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    /**
     * Menentukan apakah barang aktif.
     */
    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    /**
     * Menentukan apakah barang dapat digunakan
     * untuk transaksi baru.
     *
     * Syarat:
     * - belum soft delete;
     * - aktif;
     * - memiliki kategori;
     * - kategori aktif;
     * - kategori belum soft delete.
     */
    public function canBeUsedForNewTransaction(): bool
    {
        $kategori = $this->kategoriBarang;

        return $this->deleted_at === null
            && $this->is_active === true
            && $kategori !== null
            && $kategori->deleted_at === null
            && $kategori->isActive();
    }

    /**
     * Alias khusus konteks Pengajuan Barang.
     *
     * Opsional, tetapi membuat pemanggilan dari
     * modul Pengajuan lebih mudah dibaca.
     */
    public function canBeUsedForNewPengajuan(): bool
    {
        return $this->canBeUsedForNewTransaction();
    }

    /**
     * Menentukan apakah kategori barang aktif.
     */
    public function kategoriIsActive(): bool
    {
        return $this->kategoriBarang?->isActive() ?? false;
    }

    /**
     * Nama kategori barang.
     */
    public function getNamaKategoriAttribute(): ?string
    {
        return $this->kategoriBarang?->nama_kategori;
    }

    /**
     * Menentukan apakah barang boleh dihapus
     * secara permanen.
     *
     * Barang yang sudah pernah digunakan
     * dalam transaksi tidak boleh di-force delete.
     */
    public function canBeForceDeleted(): bool
    {
        return !$this->pengajuanBarang()
            ->withTrashed()
            ->exists();
    }

    /**
     * Proteksi physical delete.
     */
    protected static function booted(): void
    {
        static::forceDeleting(
            function (MasterBarang $barang): void {
                if (!$barang->canBeForceDeleted()) {
                    throw new \RuntimeException(
                        'Barang tidak dapat dihapus secara permanen '
                        . 'karena sudah digunakan pada transaksi '
                        . 'pengajuan barang.'
                    );
                }
            }
        );
    }
}
