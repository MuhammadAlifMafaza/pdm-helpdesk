<?php

namespace App\Models\Modules\Laporan\Models;

use App\Models\Modules\Perbaikan\Models\LogPerbaikan;
use App\Models\Modules\Perbaikan\Models\TiketPerbaikan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class LaporanPerbaikan extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Configuration
    |--------------------------------------------------------------------------
    */

    protected $table = 'view_laporan_service';

    protected $primaryKey = 'no_tiket';

    public $incrementing = false;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
        'durasi_pengerjaan_menit' => 'integer',
    ];

    /** View laporan hanya menampilkan tiket yang belum di-soft-delete. */
    protected static function booted(): void
    {
        static::addGlobalScope('tiket_aktif', function (Builder $query): void {
            $query->whereHas('tiket');
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationship
    |--------------------------------------------------------------------------
    */
    public function tiket()
    {
        return $this->belongsTo(
            TiketPerbaikan::class,
            'no_tiket',
            'id'
        );
    }

    public function logs()
    {
        return $this->hasMany(
            LogPerbaikan::class,
            'tiket_id',
            'no_tiket'
        );
    }
    /*
    |--------------------------------------------------------------------------
    | Scope Search
    |--------------------------------------------------------------------------
    */

    public function scopeSearchLaporan(
        Builder $query,
        string $search
    ): Builder {

        return $query->where(function ($query) use ($search) {

            $query

                ->where('no_tiket', 'like', "%{$search}%")
                ->orWhere('nama_pemohon', 'like', "%{$search}%")
                ->orWhere('nama_teknisi', 'like', "%{$search}%")
                ->orWhere('lokasi', 'like', "%{$search}%")
                ->orWhere('kepemilikan', 'like', "%{$search}%")
                ->orWhere('status', 'like', "%{$search}%");

            $query->orWhereHas('tiket', function ($q) use ($search) {

                $q
                    ->where('keluhan', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%");

            });

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Scope Filter Data Laporan Perbaikan
    |--------------------------------------------------------------------------
    */

    /* Filter Data Periode perbaikan */
    public function scopePeriode(
        Builder $query,
        ?string $from,
        ?string $until
    ): Builder {

        return $query
            ->when(
                $from,
                fn (Builder $query) => $query->whereDate(
                    'waktu_mulai',
                    '>=',
                    $from
                )
            )
            ->when(
                $until,
                fn (Builder $query) => $query->whereDate(
                    'waktu_mulai',
                    '<=',
                    $until
                )
            );

    }

    /* Scope Filter Status */

    public function scopeStatus(
        Builder $query,
        ?string $status
    ): Builder {

        return $query->when(

            filled($status),

            fn (Builder $query) => $query->where(
                'status',
                $status
            )

        );

    }

    /* Scope Filter Teknisi */
    public function scopeTeknisi(
        Builder $query,
        ?string $teknisi
    ): Builder {
        return $query->when(
            filled($teknisi),
            fn (Builder $query) => $query->where(
                'nama_teknisi',
                $teknisi
            )
        );
    }

    /* Scope Filter Lokasi */
    public function scopeLokasi(
        Builder $query,
        ?string $lokasi
    ): Builder {

        return $query->when(

            filled($lokasi),

            fn (Builder $query) => $query->where(
                'lokasi',
                $lokasi
            )

        );

    }

    /* Scope Filter Kepemilikan */

    public function scopeKepemilikan(
        Builder $query,
        ?string $kepemilikan
    ): Builder {

        return $query->when(

            filled($kepemilikan),

            fn (Builder $query) => $query->where(
                'kepemilikan',
                $kepemilikan
            )

        );

    }

    /*
    |--------------------------------------------------------------------------
    | Scope Filter Kategori Durasi
    |--------------------------------------------------------------------------
    */

    public function scopeKategoriDurasi(
        Builder $query,
        ?string $kategori
    ): Builder {

        if (blank($kategori)) {

            return $query;

        }

        return match ($kategori) {

            'Cepat' => $query->whereBetween(
                'durasi_pengerjaan_menit',
                [1, 60]
            ),

            'Normal' => $query->whereBetween(
                'durasi_pengerjaan_menit',
                [61, 240]
            ),

            'Lama' => $query->where(
                'durasi_pengerjaan_menit',
                '>',
                240
            ),

            'Belum Selesai' => $query->whereNull(
                'durasi_pengerjaan_menit'
            ),

            default => $query,
        };

    }

    /*
    |--------------------------------------------------------------------------
    | Scope Filter Outcome
    |--------------------------------------------------------------------------
    */

    public function scopeOutcome(
        Builder $query,
        ?string $outcome
    ): Builder {

        if (blank($outcome)) {

            return $query;

        }

        return $query->whereHas(
            'tiket',
            fn (Builder $query) => $query->whereRaw('1 = 1')
        );

    }

    /*
    |--------------------------------------------------------------------------
    | Scope Status
    |--------------------------------------------------------------------------
    */

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'Open');
    }

    public function scopeProgress(Builder $query): Builder
    {
        return $query->where('status', 'In Progress');
    }

    public function scopeClosed(Builder $query): Builder
    {
        return $query->where('status', 'Close');
    }

    /*
    |--------------------------------------------------------------------------
    | Scope Date
    |--------------------------------------------------------------------------
    */

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('waktu_mulai', today());
    }

    public function scopeThisMonth(Builder $query): Builder
    {
        return $query
            ->whereMonth('waktu_mulai', now()->month)
            ->whereYear('waktu_mulai', now()->year);
    }

    public function scopeThisYear(Builder $query): Builder
    {
        return $query
            ->whereYear('waktu_mulai', now()->year);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Kode Tiket
    |--------------------------------------------------------------------------
    */

    public function getKodeTiketAttribute(): string
    {
        // Jangan membangkitkan ulang kode berdasarkan waktu mulai SLA.
        return $this->tiket?->kode_tiket
            ?? 'TK-'.$this->no_tiket;
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Kepemilikan
    |--------------------------------------------------------------------------
    */

    public function getOwnershipLabelAttribute(): string
    {
        return $this->kepemilikan;
    }

    public function getOwnershipColorAttribute(): string
    {
        return match ($this->kepemilikan) {
            'Inventaris Kantor' => 'success',
            'Pribadi' => 'warning',
            default => 'gray',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Status
    |--------------------------------------------------------------------------
    */

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'Open' => 'Belum Dikerjakan',
            'In Progress' => 'Sedang Dikerjakan',
            'Close' => 'Selesai',
            default => '-',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'Open' => 'info',
            'In Progress' => 'warning',
            'Close' => 'success',
            default => 'gray',
        };
    }

    public function getStatusIconAttribute(): string
    {
        return match ($this->status) {
            'Open' => 'heroicon-o-clock',
            'In Progress' => 'heroicon-o-wrench-screwdriver',
            'Close' => 'heroicon-o-check-circle',
            default => 'heroicon-o-question-mark-circle',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Outcome
    |--------------------------------------------------------------------------
    */

    public function getOutcomeAttribute(): ?string
    {
        return $this->tiket?->status_outcome;
    }

    public function getOutcomeLabelAttribute(): string
    {
        return match ($this->outcome) {
            'Completed' => 'Berhasil',
            'Rejected' => 'Ditolak',
            default => '-',
        };
    }

    public function getOutcomeColorAttribute(): string
    {
        return match ($this->outcome) {
            'Completed' => 'success',
            'Rejected' => 'danger',
            default => 'gray',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Durasi Waktu Pengerjaan
    |--------------------------------------------------------------------------
    */

    public static function formatDuration(
        ?int $minutes
    ): string {

        if (
            empty($minutes) ||
            $minutes <= 0
        ) {

            return 'Sedang Dikerjakan';

        }

        $totalMenit = $minutes;

        $bulan = floor($totalMenit / (60 * 24 * 30));
        $totalMenit %= (60 * 24 * 30);

        $minggu = floor($totalMenit / (60 * 24 * 7));
        $totalMenit %= (60 * 24 * 7);

        $hari = floor($totalMenit / (60 * 24));
        $totalMenit %= (60 * 24);

        $jam = floor($totalMenit / 60);

        $menit = $totalMenit % 60;

        $hasil = [];

        /*
        |--------------------------------------------------------------------------
        | >= Bulan
        | tampilkan:
        | Bulan Minggu Hari
        |--------------------------------------------------------------------------
        */

        if ($bulan > 0) {

            $hasil[] = "{$bulan} Bulan";

            if ($minggu > 0) {
                $hasil[] = "{$minggu} Minggu";
            }

            if ($hari > 0) {
                $hasil[] = "{$hari} Hari";
            }

            return implode(' ', $hasil);

        }

        /*
        |--------------------------------------------------------------------------
        | >= Minggu
        | tampilkan:
        | Minggu Hari
        |--------------------------------------------------------------------------
        */

        if ($minggu > 0) {

            $hasil[] = "{$minggu} Minggu";

            if ($hari > 0) {
                $hasil[] = "{$hari} Hari";
            }

            return implode(' ', $hasil);

        }

        /*
        |--------------------------------------------------------------------------
        | >= Hari
        |--------------------------------------------------------------------------
        */

        if ($hari > 0) {

            $hasil[] = "{$hari} Hari";

            // if ($jam > 0) {
            //     $hasil[] = "{$jam} Jam";
            // }

            // if ($menit > 0) {
            //     $hasil[] = "{$menit} Menit";
            // }

            return implode(' ', $hasil);

        }

        /*
        |--------------------------------------------------------------------------
        | >= Jam
        |--------------------------------------------------------------------------
        */

        if ($jam > 0) {

            $hasil[] = "{$jam} Jam";

            if ($menit > 0) {
                $hasil[] = "{$menit} Menit";
            }

            return implode(' ', $hasil);

        }

        return "{$menit} Menit";

    }

    public function getDurasiAttribute(): string
    {
        return static::formatDuration(
            $this->durasi_pengerjaan_menit
        );
    }

    public function getDurasiJamAttribute(): float
    {
        if (! $this->durasi_pengerjaan_menit) {
            return 0;
        }

        return round(
            $this->durasi_pengerjaan_menit / 60,
            2
        );
    }

    public function getDurasiHariAttribute(): float
    {
        if (! $this->durasi_pengerjaan_menit) {
            return 0;
        }

        return round(
            $this->durasi_pengerjaan_menit / 1440,
            2
        );
    }

    public function getDurasiMingguAttribute(): float
    {
        if (! $this->durasi_pengerjaan_menit) {
            return 0;
        }

        return round(
            $this->durasi_pengerjaan_menit / 10080,
            2
        );
    }

    public function getDurasiBulanAttribute(): float
    {
        if (! $this->durasi_pengerjaan_menit) {
            return 0;
        }

        return round(
            $this->durasi_pengerjaan_menit / 43200,
            2
        );
    }

    public function getDurasiLevelAttribute(): string
    {
        if (! $this->durasi_pengerjaan_menit) {
            return 'Belum Selesai';
        }

        return match (true) {
            $this->durasi_pengerjaan_menit >= 43200 => 'Bulan',
            $this->durasi_pengerjaan_menit >= 10080 => 'Minggu',
            $this->durasi_pengerjaan_menit >= 1440 => 'Hari',
            $this->durasi_pengerjaan_menit >= 60 => 'Jam',
            default => 'Menit',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Waktu Mulai
    |--------------------------------------------------------------------------
    */

    public function getTanggalMulaiAttribute(): ?string
    {
        return $this->waktu_mulai?->translatedFormat('d M Y');
    }

    public function getJamMulaiAttribute(): ?string
    {
        return $this->waktu_mulai?->format('H:i:s');
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Waktu Selesai
    |--------------------------------------------------------------------------
    */

    public function getTanggalSelesaiAttribute(): ?string
    {
        return $this->waktu_selesai
            ?->translatedFormat('d M Y');
    }

    public function getJamSelesaiAttribute(): ?string
    {
        return $this->waktu_selesai
            ?->format('H:i:s');
    }

    /*
    |--------------------------------------------------------------------------
    | Statistics
    |--------------------------------------------------------------------------
    */

    /* Total Jumlah Tiket */
    public static function getTotalTiket(
        ?Builder $query = null
    ): int {

        $query ??= static::query();

        return (clone $query)->count();

    }

    /* Total Tiket Yang Belum di Kerjakan (Open) */
    public static function getTotalOpen(
        ?Builder $query = null
    ): int {

        $query ??= static::query();

        return (clone $query)
            ->where(
                'status',
                'Open'
            )
            ->count();
    }

    /* Total Tiket Yang Sedang di Kerjakan (In Progress) */
    public static function getTotalInProgress(
        ?Builder $query = null
    ): int {

        $query ??= static::query();

        return (clone $query)
            ->where(
                'status',
                'In Progress'
            )
            ->count();
    }

    /* Total Tiket Yang Telah di Tutup (Close) */
    public static function getTotalClose(
        ?Builder $query = null
    ): int {

        $query ??= static::query();

        return (clone $query)

            ->where(
                'status',
                'Close'
            )

            ->count();

    }

    /* Jumlah Tiket Yang Belum Selesai */
    public static function getTotalBelumSelesai(
        ?Builder $query = null
    ): int {

        $query ??= static::query();

        return (clone $query)
            ->whereNull(
                'waktu_selesai'
            )
            ->count();
    }

    /* Jumlah Teknisi Aktif */
    public static function getTotalTeknisi(
        ?Builder $query = null
    ): int {

        $query ??= static::query();

        return (clone $query)

            ->whereNotNull(
                'nama_teknisi'
            )
            ->distinct()
            ->count('nama_teknisi');
    }

    /* Total Lokasi */
    public static function getTotalLokasi(
        ?Builder $query = null
    ): int {

        $query ??= static::query();

        return (clone $query)

            ->distinct()

            ->count('lokasi');

    }

    /* Rata-rata Durasi Pengerjaan (Jam) Tiket */
    public static function getAverageDuration(
        ?Builder $query = null
    ): float {

        $query ??= static::query();

        $minutes = (clone $query)
            ->whereNotNull('durasi_pengerjaan_menit')
            ->avg('durasi_pengerjaan_menit');

        if (! $minutes) {
            return 0;
        }

        return round($minutes / 60, 2);

    }

    /* Average Duration Human */
    public static function getAverageDurationHuman(
        ?Builder $query = null
    ): string {

        $hours = static::getAverageDuration($query);

        if ($hours <= 0) {
            return '-';
        }

        $days = round($hours / 24, 2);

        return number_format($hours, 2)
            .' Jam'
            ." ({$days} Hari)";

    }

    /* Average Duration Description */
    public static function getAverageDurationDescription(
        ?Builder $query = null
    ): string {

        $query ??= static::query();

        $minutes = (clone $query)
            ->whereNotNull('durasi_pengerjaan_menit')
            ->avg('durasi_pengerjaan_menit');

        if (! $minutes) {
            return '-';
        }

        $days = round($minutes / 1440, 2);

        return "≈ {$days} Hari";
    }

    /*
    |--------------------------------------------------------------------------
    | Service Category
    |--------------------------------------------------------------------------
    */

    public function getServiceCategoryAttribute(): string
    {
        if (! $this->durasi_pengerjaan_menit) {
            return 'Belum Selesai';
        }

        return match (true) {
            $this->durasi_pengerjaan_menit <= 60 => 'Cepat',

            $this->durasi_pengerjaan_menit <= 240 => 'Normal',

            default => 'Lama',
        };
    }

    public function getServiceCategoryColorAttribute(): string
    {
        return match ($this->service_category) {
            'Cepat' => 'success',
            'Normal' => 'warning',
            'Lama' => 'danger',
            default => 'gray',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Summary
    |--------------------------------------------------------------------------
    */

    public function getSummaryAttribute(): string
    {
        return sprintf(
            '%s | %s | %s | %s',
            $this->tiket?->kode_tiket,
            $this->nama_pemohon,
            $this->status_label,
            $this->durasi
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Teknisi
    |--------------------------------------------------------------------------
    */

    public function getNamaTeknisiLabelAttribute(): string
    {
        return $this->nama_teknisi
            ?: 'Belum Ada Teknisi';
    }

    public function getTeknisiLabelAttribute(): string
    {
        return $this->nama_teknisi ?: 'Belum Ditugaskan';
    }

    public function getHasTeknisiAttribute(): bool
    {
        return filled($this->nama_teknisi);
    }

    /*
    |--------------------------------------------------------------------------
    | Report Helper
    |--------------------------------------------------------------------------
    */

    public function getIsFinishedAttribute(): bool
    {
        return $this->status === 'Close';
    }

    public function getIsProgressAttribute(): bool
    {
        return $this->status === 'In Progress';
    }

    public function getIsOpenAttribute(): bool
    {
        return $this->status === 'Open';
    }

    /*
    |--------------------------------------------------------------------------
    | Export Helper
    |--------------------------------------------------------------------------
    */

    public function getCanExportAttribute(): bool
    {
        return $this->is_finished;
    }

    public function getExportStatusAttribute(): string
    {
        return $this->status_label;
    }

    public function getExportDurasiAttribute(): string
    {
        return $this->durasi;
    }

    public function getExportOutcomeAttribute(): string
    {
        return $this->outcome_label;
    }

    public function getExportTeknisiAttribute(): string
    {
        return $this->nama_teknisi ?? '-';
    }
}
