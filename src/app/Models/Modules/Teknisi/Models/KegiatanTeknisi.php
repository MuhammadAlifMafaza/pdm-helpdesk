<?php

namespace App\Models\Modules\Teknisi\Models;

use App\Models\Modules\Master\Ruangan\Models\MasterRuangan;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class KegiatanTeknisi extends Model
{
    use SoftDeletes;

    protected $table = 'log_harian_teknisi';

    protected $casts = [
        'tanggal' => 'date',
    ];

    protected $fillable = [
        'teknisi_id',
        'ruangan_id',
        'tanggal',
        'deskripsi_kegiatan',
    ];

    public function teknisi()
    {
        return $this->belongsTo(
            User::class,
            'teknisi_id'
        );
    }

    public function ruangan()
    {
        return $this->belongsTo(
            MasterRuangan::class,
            'ruangan_id'
        );
    }

    public function petugas(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'kegiatan_petugas',
            'kegiatan_id',
            'petugas_id'
        )->withTimestamps();
    }

    protected static function booted(): void
    {
        static::forceDeleted(function (self $kegiatan): void {
            $kegiatan->petugas()->detach();
        });
    }
}
