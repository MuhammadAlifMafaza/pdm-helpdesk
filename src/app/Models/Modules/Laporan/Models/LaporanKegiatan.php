<?php

namespace App\Models\Modules\Laporan\Models;

use App\Models\Modules\Teknisi\Models\KegiatanTeknisi;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class LaporanKegiatan extends Model
{
    protected $table = 'view_laporan_kegiatan';

    protected $primaryKey = 'id_log';

    public $incrementing = false;

    protected $keyType = 'int';

    public $timestamps = false;

    // Kita kosongkan casts agar bisa menggunakan fallback accessor
    protected $casts = [];

    protected static function booted(): void
    {
        static::addGlobalScope('kegiatan_aktif', function (Builder $query): void {
            $query->whereHas('kegiatan');
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationship
    |--------------------------------------------------------------------------
    */
    public function kegiatan()
    {
        return $this->belongsTo(
            KegiatanTeknisi::class,
            'id_log',
            'id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Scope Search & Filter
    |--------------------------------------------------------------------------
    */
    public function scopeSearchLaporan(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('nama_teknisi', 'like', "%{$search}%")
                ->orWhere('deskripsi_kegiatan', 'like', "%{$search}%");
        });
    }

    public function scopePeriode(Builder $query, ?string $from, ?string $until): Builder
    {
        return $query
            ->when($from, fn (Builder $q) => $q->whereDate('tanggal', '>=', $from))
            ->when($until, fn (Builder $q) => $q->whereDate('tanggal', '<=', $until));
    }

    /*
    |--------------------------------------------------------------------------
    | Accessor (Helper Cadangan / Fallback)
    |--------------------------------------------------------------------------
    */

    // Mengambil tanggal asli dari view atau meminjam dari relasi
    public function getTanggalKegiatanAttribute()
    {
        $tgl = $this->attributes['tanggal'] ?? null;

        return $tgl ? Carbon::parse($tgl) : $this->kegiatan?->tanggal;
    }

    // Ambil deskripsi dan lokasi dari sumber modul kegiatan yang sebenarnya.
    public function getDeskripsiAttribute(): ?string
    {
        return $this->attributes['deskripsi_kegiatan'] ?? $this->kegiatan?->deskripsi_kegiatan;
    }

    public function getLokasiAttribute(): ?string
    {
        return $this->kegiatan?->ruangan?->nama_ruangan;
    }
}
