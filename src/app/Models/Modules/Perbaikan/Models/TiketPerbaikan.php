<?php

namespace App\Models\Modules\Perbaikan\Models;

// Imports necesery Modules or Classes
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Events\HelpdeskActivityCreated;

// Imports necesery Files(Models)
use App\Models\User;
use App\Models\Modules\Master\Ruangan\Models\MasterRuangan;
use App\Models\Modules\Perbaikan\Enums\TicketStatus;

class TiketPerbaikan extends Model
{
    use SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | Model Configuration
    |--------------------------------------------------------------------------
    */
    protected $table = 'tiket_perbaikan';

    protected $appends = [
        'kode_tiket',
        'status_outcome',
        'waktu_mulai',
        'waktu_selesai',
        'durasi_pengerjaan',
    ];

    /*
    |--------------------------------------------------------------------------
    | Model Fillable Fields
    |--------------------------------------------------------------------------
    */
    /**
     * Status konfirmasi penyelesaian pekerjaan oleh pemohon.
     */
    public const KONFIRMASI_MENUNGGU = 'menunggu';

    public const KONFIRMASI_DISETUJUI = 'disetujui';

    public const KONFIRMASI_DITOLAK = 'ditolak_pemohon';

    private const ALLOWED_UPDATE_FIELDS = [
        'kepemilikan',
        'ruangan_id',
        'deskripsi',
        'keluhan',
    ];

    protected $fillable = [
        'user_id',
        'ruangan_id',
        'keluhan',
        'kepemilikan',
        'deskripsi',
        'status',
        'konfirmasi_status',
        'konfirmasi_at',
        'konfirmasi_catatan',
    ];

    protected function casts(): array
    {
        return [
            'konfirmasi_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }

    public function ruangan(): BelongsTo
    {
        return $this->belongsTo(
            MasterRuangan::class,
            'ruangan_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Ticket Identity (Kodes Tiket) Generation
    |--------------------------------------------------------------------------
    */
    public function getKodeTiketAttribute(): string
    {
        $firstIdToday = self::withTrashed()
            ->whereDate(
                'created_at',
                $this->created_at->toDateString()
            )
            ->min('id');

        $nomorUrut = ($this->id - $firstIdToday) + 1;

        return sprintf(
            'TK-%s-%04d',
            $this->created_at->format('dmY'),
            $nomorUrut
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Model Lifecycle
    |--------------------------------------------------------------------------
    */
    protected static function booted(): void
    {
        static::created(function (self $tiket): void {

            $tiket->tambahLog(
                kategori: 'Status',
                lama: null,
                baru: 'Open',
                keterangan: 'Tiket dibuat'
            );

            HelpdeskActivityCreated::dispatch(
                module: 'perbaikan',
                activity: 'created',
                referenceId: $tiket->id,
                kode: $tiket->kode_tiket,
                actorId: $tiket->user_id,
                data: [
                    'message' => "Tiket {$tiket->kode_tiket} baru telah dibuat.",
                    'user_id' => $tiket->user_id,
                    'user_name' => $tiket->user?->name,
                    'ruangan' => $tiket->ruangan?->nama_ruangan,
                    'keluhan' => $tiket->keluhan,
                ],
            );
        });

        static::deleting(function (self $tiket): void {

            if ($tiket->isForceDeleting()) {
                return;
            }

            $namaUser = auth()->user()?->name ?? 'System';

            $tiket->tambahLog(
                kategori: 'Delete Data',
                lama: $tiket->status,
                baru: 'Deleted',
                keterangan: "Tiket telah dihapus oleh {$namaUser}"
            );

        });
    }

    /*
    |--------------------------------------------------------------------------
    | Permission / Access Control
    |--------------------------------------------------------------------------
    */
    public function canBeAccessedBy(?User $user = null): bool
    {
        $user ??= auth()->user();

        if (!$user) {
            return false;
        }

        /*
         * Staff Helpdesk
         */
        if (
            $user->hasAnyRole([
                'admin',
                'teknisi',
                'admin_super',
                'super_admin',
            ])
        ) {
            return true;
        }

        /*
         * Pemohon hanya dapat mengakses tiket miliknya.
         */
        if ($user->hasRole('pemohon')) {
            return (int) $this->user_id === (int) $user->id;
        }

        return false;
    }

    public function canStaffEdit(): bool
    {
        if (
            auth()->user()->hasRole('admin')
            || auth()->user()->hasRole('super_admin')
        ) {
            return true;
        }

        return !$this->isClosed();
    }

    public function canPemohonEdit(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        if (!$user->hasRole('pemohon')) {
            return false;
        }

        /* Pemohon hanya boleh mengubah tiket miliknya sendiri */

        if ((int) $this->user_id !== (int) $user->id) {
            return false;
        }

        /* Hanya tiket dengan status Open yang dapat diedit */

        return $this->isOpen();
    }

    /**
     * Apakah tiket dapat dibatalkan oleh Pemohon?
     */
    public function canPemohonDelete(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        if (!$user->hasRole('pemohon')) {
            return false;
        }

        /* Hanya pemilik tiket */
        if ((int) $this->user_id !== (int) $user->id) {
            return false;
        }

        /* Tiket hanya dapat dibatalkan apabila status tiket masih Open */
        return $this->isOpen();
    }

    /*
    |--------------------------------------------------------------------------
    | Log Configuration
    |--------------------------------------------------------------------------
    */
    public function logs(): HasMany
    {
        return $this->hasMany(
            LogPerbaikan::class,
            'tiket_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Audit Logs
    |--------------------------------------------------------------------------
    */
    public function tambahLog(
        string $kategori,
        ?string $lama = null,
        ?string $baru = null,
        ?string $keterangan = null
    ): LogPerbaikan {

        return $this->logs()->create([
            'user_id' => auth()->id() ?? $this->user_id,
            'kategori_log' => $kategori,
            'data_lama' => $lama,
            'data_baru' => $baru,
            'keterangan' => $keterangan,
            'created_at' => now(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Status Management
    |--------------------------------------------------------------------------
    */
    public function scopeOpen(Builder $query): void
    {
        $query->where('status', TicketStatus::OPEN->value);
    }

    public function scopeProgress(Builder $query): void
    {
        $query->where('status', TicketStatus::IN_PROGRESS->value);
    }

    public function scopeClosed(Builder $query): void
    {
        $query->where('status', TicketStatus::CLOSE->value);
    }

    public function isLocked(): bool
    {
        return $this->status === 'Close';
    }

    public function updateStatus(
        string $statusBaru,
        ?string $catatan = null,
        string $activity = 'status',
        ?string $message = null
    ): bool {

        $statusLama = $this->status;

        if ($statusLama === $statusBaru) {
            return false;
        }

        // Transisi keluar dari siklus Close (mis. reopen) membersihkan
        // sisa state konfirmasi agar tidak tertinggal pada tiket terbuka.
        if ($statusBaru !== TicketStatus::CLOSE->value) {
            $this->konfirmasi_status = null;
            $this->konfirmasi_at = null;
            $this->konfirmasi_catatan = null;
        }

        $this->update([
            'status' => $statusBaru,
        ]);

        $log = $this->tambahLog(
            kategori: 'Status',
            lama: $statusLama,
            baru: $statusBaru,
            keterangan: $catatan
        );

        HelpdeskActivityCreated::dispatch(
            module: 'perbaikan',
            activity: $activity,
            referenceId: $this->id,
            kode: $this->kode_tiket,
            actorId: auth()->id(),
            data: [
                'old_status' => $statusLama,
                'new_status' => $statusBaru,
                'message' => $message
                ?? "Status {$this->kode_tiket} berubah dari {$statusLama} menjadi {$statusBaru}.",
                'log_id' => $log->id,
            ],
        );

        return true;
    }

    public function reopen(
        ?string $catatan = null
    ) {
        return $this->updateStatus(
            'In Progress',
            '[REOPEN] ' .
            ($catatan ?? 'Tiket dibuka kembali')
        );
    }

    public function pending(string $catatan)
    {
        return $this->tambahLog(
            'Pending',
            null,
            null,
            "[PENDING] {$catatan}"
        );
    }

    public function closeAsCompleted(
        ?string $catatan = null
    ) {
        $this->resetKonfirmasi();

        return $this->updateStatus(
            'Close',
            '[SELESAI] ' . ($catatan ?? '')
        );
    }

    public function closeAsRejected(
        ?string $catatan = null
    ) {
        $this->resetKonfirmasi();

        $pesan = 'Layanan ' . $this->kode_tiket . ' ditolak'
            . (filled($catatan) ? ': ' . trim($catatan) : '.');

        return $this->updateStatus(
            'Close',
            '[DITOLAK] ' . ($catatan ?? ''),
            activity: 'rejected',
            message: $pesan
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Konfirmasi Penyelesaian Pekerjaan
    |--------------------------------------------------------------------------
    */

    /**
     * Staf menandai pekerjaan selesai dan meminta konfirmasi dari pemohon.
     * Status tiket tetap In Progress sampai pemohon memutuskan.
     */
    public function mintaKonfirmasiSelesai(
        ?string $catatan = null
    ): bool {

        if ($this->isClosed()) {
            return false;
        }

        $this->konfirmasi_status = self::KONFIRMASI_MENUNGGU;
        $this->konfirmasi_at = now();
        $this->konfirmasi_catatan = $catatan;
        $this->save();

        $log = $this->tambahLog(
            kategori: 'Konfirmasi',
            lama: $this->status,
            baru: 'Menunggu Konfirmasi',
            keterangan: '[MENUNGGU KONFIRMASI] ' . ($catatan ?? '')
        );

        HelpdeskActivityCreated::dispatch(
            module: 'perbaikan',
            activity: 'confirmation_requested',
            referenceId: $this->id,
            kode: $this->kode_tiket,
            actorId: auth()->id(),
            data: [
                'message' => "Pekerjaan {$this->kode_tiket} telah selesai dikerjakan. Mohon konfirmasi penyelesaian.",
                'catatan' => $catatan,
                'log_id' => $log->id,
            ],
        );

        return true;
    }

    /**
     * Pemohon mengonfirmasi pekerjaan selesai → tiket ditutup sebagai selesai.
     */
    public function konfirmasiSelesai(
        ?string $catatan = null
    ): bool {

        if (! $this->isMenungguKonfirmasi()) {
            return false;
        }

        $this->konfirmasi_status = self::KONFIRMASI_DISETUJUI;
        $this->konfirmasi_at = now();
        $this->konfirmasi_catatan = $catatan;
        $this->save();

        return $this->updateStatus(
            'Close',
            '[SELESAI] ' . ($catatan ?? ''),
            activity: 'confirmed',
            message: "Penyelesaian {$this->kode_tiket} dikonfirmasi oleh pemohon."
        );
    }

    /**
     * Pemohon menolak konfirmasi dan meminta revisi → tiket tetap In Progress.
     */
    public function tolakKonfirmasi(
        string $catatan
    ): bool {

        if (! $this->isMenungguKonfirmasi()) {
            return false;
        }

        $this->konfirmasi_status = self::KONFIRMASI_DITOLAK;
        $this->konfirmasi_at = now();
        $this->konfirmasi_catatan = $catatan;
        $this->save();

        $log = $this->tambahLog(
            kategori: 'Konfirmasi',
            lama: 'Menunggu Konfirmasi',
            baru: 'Ditolak',
            keterangan: '[KONFIRMASI DITOLAK] ' . $catatan
        );

        HelpdeskActivityCreated::dispatch(
            module: 'perbaikan',
            activity: 'confirmation_rejected',
            referenceId: $this->id,
            kode: $this->kode_tiket,
            actorId: auth()->id(),
            data: [
                'message' => "Penyelesaian {$this->kode_tiket} ditolak oleh pemohon. Alasan: {$catatan}",
                'catatan' => $catatan,
                'log_id' => $log->id,
            ],
        );

        return true;
    }

    public function isMenungguKonfirmasi(): bool
    {
        return $this->konfirmasi_status === self::KONFIRMASI_MENUNGGU
            && ! $this->isClosed();
    }

    public function isKonfirmasiDisetujui(): bool
    {
        return $this->konfirmasi_status === self::KONFIRMASI_DISETUJUI;
    }

    public function isKonfirmasiDitolak(): bool
    {
        return $this->konfirmasi_status === self::KONFIRMASI_DITOLAK;
    }

    protected function resetKonfirmasi(): void
    {
        $this->konfirmasi_status = null;
        $this->konfirmasi_at = null;
        $this->konfirmasi_catatan = null;
    }

    /*
    |--------------------------------------------------------------------------
    | Chat
    |--------------------------------------------------------------------------
    */
    public function chatLogs(): HasMany
    {
        return $this->logs()
            ->where('kategori_log', 'Chat');
    }

    public function sendMessage(
        string $pesan
    ): LogPerbaikan {

        $log = $this->tambahLog(
            kategori: 'Chat',
            lama: null,
            baru: null,
            keterangan: $pesan
        );

        HelpdeskActivityCreated::dispatch(
            module: 'perbaikan',
            activity: 'chat',
            referenceId: $this->id,
            kode: $this->kode_tiket,
            actorId: auth()->id(),
            data: [
                'message' => $pesan,
                'sender_id' => auth()->id(),
                'sender_name' => auth()->user()?->name,
                'log_id' => $log->id,
            ],
        );

        return $log;
    }

    /*
    |--------------------------------------------------------------------------
    | Data Management
    |--------------------------------------------------------------------------
    */
    public function updateDataPemohon(array $data): bool
    {
        if (!$this->canPemohonEdit()) {
            return false;
        }

        $updated = false;

        foreach (
            self::ALLOWED_UPDATE_FIELDS as $field
        ) {

            if (!array_key_exists($field, $data)) {
                continue;
            }

            $updated = $this->updateField(
                field: $field,
                valueBaru: $data[$field],
                catatan: 'Data diperbarui oleh pemohon'
            ) || $updated;
        }

        return $updated;
    }

    public function updateField(
        string $field,
        mixed $valueBaru,
        ?string $catatan = null
    ): bool {

        if (
            !in_array(
                $field,
                self::ALLOWED_UPDATE_FIELDS,
                true
            )
        ) {
            throw new \InvalidArgumentException(
                "Field {$field} tidak boleh diubah."
            );
        }

        $valueLama = $this->{$field};

        if ((string) $valueLama === (string) $valueBaru) {
            return false;
        }

        $this->update([
            $field => $valueBaru,
        ]);

        $log = $this->tambahLog(
            kategori: 'Update Data',
            lama: (string) $valueLama,
            baru: (string) $valueBaru,
            keterangan: $catatan
            ?? "{$field} diperbarui oleh " . (auth()->user()?->name ?? 'System')
        );

        HelpdeskActivityCreated::dispatch(
            module: 'perbaikan',
            activity: 'updated',
            referenceId: $this->id,
            kode: $this->kode_tiket,
            actorId: auth()->id(),
            data: [
                'field' => $field,
                'old_value' => $valueLama,
                'new_value' => $valueBaru,
                'message' => "Data {$this->kode_tiket} diperbarui.",
                'log_id' => $log->id,
            ],
        );

        return true;
    }

    public function cancelByPemohon(
        ?string $catatan = null
    ): bool {

        if (!$this->canPemohonDelete()) {
            return false;
        }

        $this->tambahLog(
            kategori: 'Delete Data',
            lama: $this->status,
            baru: 'Cancelled',
            keterangan: $catatan
            ?? 'Tiket dibatalkan oleh pemohon'
        );

        return $this->delete();
    }

    public function updateRuangan(
        int $ruanganIdBaru
    ): bool {
        $ruanganLama = $this->ruangan?->nama_ruangan;

        $ruanganBaru = MasterRuangan::find($ruanganIdBaru);

        if (!$ruanganBaru) {
            throw new \InvalidArgumentException(
                'Ruangan tidak ditemukan.'
            );
        }

        if ((int) $this->ruangan_id === $ruanganIdBaru) {
            return false;
        }

        $this->update([
            'ruangan_id' => $ruanganIdBaru,
        ]);

        $log = $this->tambahLog(
            kategori: 'Update Data',
            lama: $ruanganLama,
            baru: $ruanganBaru->nama_ruangan,
            keterangan: 'Ruangan dipindahkan',
        );

        HelpdeskActivityCreated::dispatch(
            module: 'perbaikan',
            activity: 'updated',
            referenceId: $this->id,
            kode: $this->kode_tiket,
            actorId: auth()->id(),
            data: [
                'field' => 'ruangan_id',
                'field_label' => 'Ruangan',
                'old_value' => $ruanganLama,
                'new_value' => $ruanganBaru->nama_ruangan,
                'message' => "Ruangan {$this->kode_tiket} diperbarui.",
                'log_id' => $log->id,
            ],
        );

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Timeline
    |--------------------------------------------------------------------------
    */
    public function timeline(): HasMany
    {
        return $this->logs()
            ->with('user')
            ->latest('created_at');
    }

    /*
    |--------------------------------------------------------------------------
    | Reporting / Time Attributes
    |--------------------------------------------------------------------------
    */
    public function getWaktuMulaiAttribute(): ?Carbon
    {
        $startedAt = $this->logs()
            ->where('kategori_log', 'Status')
            ->where('data_baru', 'In Progress')
            ->orderBy('created_at')
            ->value('created_at');

        return $startedAt ? Carbon::parse($startedAt) : null;
    }

    /**
     * Waktu selesai hanya dianggap final bila status saat ini Close dan
     * log penutupan terakhir memiliki outcome [SELESAI] atau [DITOLAK].
     * Jika tiket sudah dibuka kembali, waktu selesai bernilai null.
     */
    public function getWaktuSelesaiAttribute(): ?Carbon
    {
        if (! $this->isClosed()) {
            return null;
        }

        $closeLog = $this->logs()
            ->where('kategori_log', 'Status')
            ->where('data_baru', 'Close')
            ->latest('created_at')
            ->first();

        if (! $closeLog) {
            return null;
        }

        $keterangan = $closeLog->keterangan ?? '';
        if (! str_contains($keterangan, '[SELESAI]') && ! str_contains($keterangan, '[DITOLAK]')) {
            return null;
        }

        return $closeLog->created_at ? Carbon::parse($closeLog->created_at) : null;
    }

    /** Durasi kalender total; tiket yang belum selesai terus dihitung sampai sekarang. */
    public function getDurasiPengerjaanAttribute(): ?string
    {
        $mulai = $this->waktu_mulai;
        if (! $mulai) {
            return null;
        }

        $akhir = $this->waktu_selesai ?? now();
        return $mulai->diffForHumans($akhir, true);
    }

    /*
    |--------------------------------------------------------------------------
    | Status Helpers
    |--------------------------------------------------------------------------
    */
    public function isOpen(): bool
    {
        return $this->status === 'Open';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'In Progress';
    }

    public function isClosed(): bool
    {
        return $this->status === 'Close';
    }

    public function isCompleted(): bool
    {
        return $this->status_outcome === 'Completed';
    }

    public function isRejected(): bool
    {
        return $this->status_outcome === 'Rejected';
    }

    /*
    |--------------------------------------------------------------------------
    | Outcome Helpers
    |--------------------------------------------------------------------------
    */
    public function getStatusOutcomeAttribute(): ?string
    {
        if (!$this->isClosed()) {
            return null;
        }

        $closeLog = $this->logs()
            ->where('kategori_log', 'Status')
            ->where('data_baru', 'Close')
            ->latest('created_at')
            ->first();

        if (!$closeLog) {
            return null;
        }

        if (
            str_contains(
                $closeLog->keterangan ?? '',
                '[SELESAI]'
            )
        ) {
            return 'Completed';
        }

        if (
            str_contains(
                $closeLog->keterangan ?? '',
                '[DITOLAK]'
            )
        ) {
            return 'Rejected';
        }

        return null;
    }
    /*
    |--------------------------------------------------------------------------
    | Statistics
    |--------------------------------------------------------------------------
    */

    /**
     * Total jumlah tiket.
     */
    public static function getTotalTiket(
        ?Builder $query = null
    ): int {
        $query ??= static::query();

        return (clone $query)->count();
    }

    /**
     * Total tiket yang belum dikerjakan.
     */
    public static function getTotalOpen(
        ?Builder $query = null
    ): int {
        $query ??= static::query();

        return (clone $query)
            ->where('status', 'Open')
            ->count();
    }

    /**
     * Total tiket yang sedang dikerjakan.
     */
    public static function getTotalInProgress(
        ?Builder $query = null
    ): int {
        $query ??= static::query();

        return (clone $query)
            ->where('status', 'In Progress')
            ->count();
    }

    /**
     * Total tiket yang telah ditutup.
     */
    public static function getTotalClose(
        ?Builder $query = null
    ): int {
        $query ??= static::query();

        return (clone $query)
            ->where('status', 'Close')
            ->count();
    }

    /**
     * Jumlah tiket yang belum selesai.
     */
    public static function getTotalBelumSelesai(
        ?Builder $query = null
    ): int {
        $query ??= static::query();

        return (clone $query)
            ->whereNull('waktu_selesai')
            ->count();
    }

    /**
     * Rata-rata durasi pengerjaan dalam jam.
     */
    public static function getAverageDuration(
        ?Builder $query = null
    ): float {
        $minutes = static::getAverageDurationMinutes($query);

        if ($minutes === null) {
            return 0;
        }

        return round($minutes / 60, 2);
    }

    /**
     * Rata-rata durasi pengerjaan dalam format human readable.
     */
    public static function getAverageDurationHuman(
        ?Builder $query = null
    ): string {
        $hours = static::getAverageDuration($query);

        if ($hours <= 0) {
            return '-';
        }

        $days = round($hours / 24, 2);

        return number_format($hours, 2)
            . ' Jam'
            . " ({$days} Hari)";
    }

    /**
     * Deskripsi rata-rata durasi pengerjaan.
     */
    public static function getAverageDurationDescription(
        ?Builder $query = null
    ): string {
        $minutes = static::getAverageDurationMinutes($query);

        if ($minutes === null) {
            return '-';
        }

        $days = round($minutes / 1440, 2);

        return "≈ {$days} Hari";
    }

    private static function getAverageDurationMinutes(
        ?Builder $query = null
    ): ?float {
        $query ??= static::query();

        $durations = (clone $query)
            ->with([
                'logs' => function (HasMany $query): void {
                    $query
                        ->where('kategori_log', 'Status')
                        ->orderBy('created_at');
                },
            ])
            ->get()
            ->map(function (self $tiket): ?float {
                $waktuMulai = $tiket->logs
                    ->firstWhere('data_baru', 'In Progress')
                        ?->created_at;
                $waktuSelesai = $tiket->logs
                    ->where('data_baru', 'Close')
                    ->last()
                        ?->created_at;

                if (!$waktuMulai || !$waktuSelesai) {
                    return null;
                }

                return abs($waktuMulai->diffInMinutes($waktuSelesai));
            })
            ->filter();

        return $durations->isEmpty()
            ? null
            : (float) $durations->average();
    }

    /**
     * Tiket yang pernah diambil / ditangani oleh teknisi tertentu.
     *
     * Identitas teknisi berasal dari log perubahan:
     * Open -> In Progress
     */
    public function scopeHandledByTechnician(
        Builder $query,
        int $userId
    ): Builder {
        return $query->whereHas('logs', function (Builder $logQuery) use ($userId) {
            $logQuery
                ->where('user_id', $userId)
                ->where('kategori_log', 'Status')
                ->where('data_lama', 'Open')
                ->where('data_baru', 'In Progress');
        });
    }

    /**
     * Tiket yang saat ini sedang berada pada penanganan teknisi.
     *
     * Assignment terakhir pada tiket harus dilakukan oleh teknisi tersebut.
     */
    public function scopeCurrentlyHandledByTechnician(
        Builder $query,
        int $userId
    ): Builder {
        return $query->whereExists(function ($subQuery) use ($userId) {
            $subQuery
                ->selectRaw('1')
                ->from('log_data_tiket_perbaikan as assignment_log')
                ->whereColumn(
                    'assignment_log.tiket_id',
                    'tiket_perbaikan.id'
                )
                ->where('assignment_log.user_id', $userId)
                ->where('assignment_log.kategori_log', 'Status')
                ->where('assignment_log.data_lama', 'Open')
                ->where('assignment_log.data_baru', 'In Progress')
                ->whereNotExists(function ($laterAssignment) {
                    $laterAssignment
                        ->selectRaw('1')
                        ->from(
                            'log_data_tiket_perbaikan as later_log'
                        )
                        ->whereColumn(
                            'later_log.tiket_id',
                            'assignment_log.tiket_id'
                        )
                        ->where(
                            'later_log.kategori_log',
                            'Status'
                        )
                        ->where(
                            'later_log.data_lama',
                            'Open'
                        )
                        ->where(
                            'later_log.data_baru',
                            'In Progress'
                        )
                        ->whereColumn(
                            'later_log.created_at',
                            '>',
                            'assignment_log.created_at'
                        );
                });
        });
    }
}
