<?php

namespace App\Models\Modules\Perbaikan\Models;

// use App\Models\Modules\Perbaikan\Models\TiketPerbaikan;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class LogPerbaikan extends Model
{
    protected $table = 'log_data_tiket_perbaikan';
    const STATUS = 'Status';
    const CHAT = 'Chat';
    const UPDATE_DATA = 'Update Data';
    const KONFIRMASI = 'Konfirmasi';

    public const EVENT_CREATE = 'CREATE';
    public const EVENT_ASSIGN = 'ASSIGN';
    public const EVENT_PENDING = 'PENDING';
    public const EVENT_COMPLETE = 'COMPLETE';
    public const EVENT_REJECT = 'REJECT';
    public const EVENT_REOPEN = 'REOPEN';
    public const EVENT_CHAT = 'CHAT';
    public const EVENT_UPDATE = 'UPDATE';
    public const EVENT_DELETE = 'DELETE';
    public const EVENT_STATUS = 'STATUS';
    public const EVENT_SYSTEM = 'SYSTEM';
    public const EVENT_KONFIRMASI = 'KONFIRMASI';

    public $timestamps = false;

    protected $fillable = [
        'tiket_id',
        'user_id',
        'kategori_log',
        'data_lama',
        'data_baru',
        'keterangan',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function tiket()
    {
        return $this->belongsTo(
            TiketPerbaikan::class,
            'tiket_id'
        )->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }

    /**
     * Summary of scopeSearchTimeline
     */
    public function scopeSearchTimeline(
        Builder $query,
        string $search
    ): Builder {

        return $query->where(function ($query) use ($search) {

            $query
                ->where('kategori_log', 'like', "%{$search}%") // Search in kategori_log
                ->orWhere('keterangan', 'like', "%{$search}%") // Search in keterangan
                ->orWhere('data_lama', 'like', "%{$search}%") // Search in data_lama
                ->orWhere('data_baru', 'like', "%{$search}%") // Search in data_baru

                // Search in related user model
                ->orWhereHas('user', function ($q) use ($search) {
                    $q
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })

                // Search in related tiket's keluhan, deskripsi, and status
                ->orWhereHas('tiket', function ($q) use ($search) {
                    $q
                        ->where('keluhan', 'like', "%{$search}%")
                        ->orWhere('deskripsi', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%")
                        ->orWhere('kepemilikan', 'like', "%{$search}%");
                })

                // Search in related tiket's ruangan model
                ->orWhereHas('tiket.user', function ($q) use ($search) {
                    $q->where(
                        'name',
                        'like',
                        "%{$search}%"
                    );
                })

                // Search in related tiket's ruangan model
                ->orWhereHas('tiket.ruangan', function ($q) use ($search) {
                    $q->where(
                        'nama_ruangan',
                        'like',
                        "%{$search}%"
                    );
                });
        });

    }

    public function scopeToday($query)
    {
        return $query->whereDate(
            'created_at',
            today()
        );
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween(
            'created_at',
            [
                now()->startOfWeek(),
                now()->endOfWeek(),
            ]
        );
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth(
            'created_at',
            now()->month
        );
    }

    public function scopeEventType(
        Builder $query,
        string $type
    ): Builder {
        return match ($type) {
            self::EVENT_CREATE => $query->created(),
            self::EVENT_ASSIGN => $query->assign(),
            self::EVENT_COMPLETE => $query->complete(),
            self::EVENT_REJECT => $query->reject(),
            self::EVENT_REOPEN => $query->reopen(),
            self::EVENT_PENDING => $query->pending(),
            self::EVENT_CHAT => $query->where('kategori_log', self::CHAT),
            self::EVENT_UPDATE => $query->where('kategori_log', self::UPDATE_DATA),
            self::EVENT_DELETE => $query->where('kategori_log', 'Delete Data'),
            self::EVENT_STATUS => $query->where('kategori_log', self::STATUS),
            self::EVENT_KONFIRMASI => $query->where('kategori_log', self::KONFIRMASI),
            self::EVENT_SYSTEM => $query->whereNotIn('kategori_log', [
                self::STATUS,
                self::CHAT,
                self::UPDATE_DATA,
                self::KONFIRMASI,
                'Delete Data',
                'Pending',
            ]),
            default => $query->whereRaw('1 = 0'),
        };

    }

    public function scopeCreated($query)
    {
        return $query
            ->where('kategori_log', 'Status')
            ->whereNull('data_lama')
            ->where('data_baru', 'Open');
    }

    public function scopeAssign($query)
    {
        return $query
            ->where('kategori_log', 'Status')
            ->where('data_lama', 'Open')
            ->where('data_baru', 'In Progress');
    }

    public function scopeComplete($query)
    {
        return $query
            ->where('kategori_log', 'Status')
            ->where('data_lama', 'In Progress')
            ->where('data_baru', 'Close')
            ->where('keterangan', 'like', '[SELESAI]%');
    }

    public function scopeReject($query)
    {
        return $query
            ->where('kategori_log', 'Status')
            ->where('data_lama', 'In Progress')
            ->where('data_baru', 'Close')
            ->where('keterangan', 'like', '[DITOLAK]%');
    }

    public function scopeReopen($query)
    {
        return $query
            ->where('kategori_log', 'Status')
            ->where('data_lama', 'Close')
            ->where('data_baru', 'In Progress');
    }

    public function scopePending($query)
    {
        return $query
            ->where('kategori_log', 'Pending');
    }

    public function getPriorityAttribute()
    {
        return match ($this->event_type) {

            self::EVENT_DELETE => 100,

            self::EVENT_REJECT => 90,

            self::EVENT_COMPLETE => 80,

            self::EVENT_REOPEN => 70,

            self::EVENT_ASSIGN => 60,

            self::EVENT_CREATE => 50,

            default => 10,
        };
    }

    public function isImportant(): bool
    {
        return in_array(
            $this->event_type,
            [
                self::EVENT_COMPLETE,
                self::EVENT_REJECT,
                self::EVENT_DELETE,
                self::EVENT_REOPEN,
            ]
        );
    }

    public function isStatusActivity(): bool
    {
        return in_array(
            $this->event_type,
            [
                self::EVENT_CREATE,
                self::EVENT_ASSIGN,
                self::EVENT_COMPLETE,
                self::EVENT_REJECT,
                self::EVENT_REOPEN,
                self::EVENT_PENDING,
            ]
        );
    }

    public function isChatActivity(): bool
    {
        return $this->event_type === self::EVENT_CHAT;
    }

    public function isUpdateActivity(): bool
    {
        return $this->event_type === self::EVENT_UPDATE;
    }

    public function isDeleteActivity(): bool
    {
        return $this->event_type === self::EVENT_DELETE;
    }

    public function getActionTypeAttribute(): string
    {
        return match ($this->event_type) {

            self::EVENT_CREATE => 'CREATE',

            self::EVENT_ASSIGN,
            self::EVENT_PENDING,
            self::EVENT_REOPEN,
            self::EVENT_UPDATE => 'UPDATE',

            self::EVENT_COMPLETE,
            self::EVENT_REJECT => 'FINISH',

            self::EVENT_DELETE => 'DELETE',

            default => 'OTHER',
        };
    }

    public function getBadgeAttribute(): array
    {
        return [

            'text' => $this->event_name,

            'icon' => $this->event_icon,

            'color' => $this->event_color,

        ];
    }

    public function getSeverityAttribute(): string
    {
        return match ($this->event_type) {

            'DELETE',
            'REJECT' => 'high',

            'PENDING',
            'REOPEN' => 'medium',

            default => 'normal',
        };
    }

    public function getCategoryAttribute(): string
    {
        return match ($this->event_type) {

            self::EVENT_CREATE,
            self::EVENT_ASSIGN,
            self::EVENT_COMPLETE,
            self::EVENT_REJECT,
            self::EVENT_REOPEN,
            self::EVENT_PENDING => 'Workflow',

            self::EVENT_CHAT => 'Communication',

            self::EVENT_UPDATE => 'Modification',

            self::EVENT_DELETE => 'System',

            default => 'Other',
        };
    }

    public function getCurrentStageAttribute(): string
    {
        return match ($this->tiket?->status) {

            'Open' => 'Menunggu Teknisi',

            'In Progress' => 'Sedang Dikerjakan',

            'Close' => match ($this->tiket?->status_outcome) {

                    'Completed' => 'Selesai',

                    'Rejected' => 'Ditolak',

                    default => 'Close',
                },

            default => '-',
        };
    }

    /**
     * Summary of HELPER for Timeline Perbaikan
     */
    public function getEventNameAttribute(): string
    {
        return match ($this->event_type) {

            self::EVENT_CREATE => 'Tiket Dibuat',

            self::EVENT_ASSIGN => 'Tiket Diambil',

            self::EVENT_PENDING => 'Tiket Ditunda',

            self::EVENT_CHAT => 'Pesan Baru',

            self::EVENT_UPDATE => 'Perubahan Data',

            self::EVENT_COMPLETE => 'Tiket Selesai',

            self::EVENT_REJECT => 'Tiket Ditolak',

            self::EVENT_REOPEN => 'Tiket Dibuka Kembali',

            self::EVENT_DELETE => 'Hapus Data',

            self::EVENT_KONFIRMASI => match ($this->data_baru) {
                'Disetujui' => 'Konfirmasi Diterima',
                'Ditolak' => 'Konfirmasi Ditolak',
                default => 'Menunggu Konfirmasi',
            },

            default => 'Aktivitas',
        };
    }

    public function getEventIconAttribute(): string
    {
        return match ($this->event_type) {

            self::EVENT_CREATE => 'heroicon-o-plus-circle',

            self::EVENT_ASSIGN => 'heroicon-o-wrench-screwdriver',

            self::EVENT_PENDING => 'heroicon-o-pause-circle',

            self::EVENT_CHAT => 'heroicon-o-chat-bubble-left-right',

            self::EVENT_UPDATE => 'heroicon-o-pencil-square',

            self::EVENT_COMPLETE => 'heroicon-o-check-badge',

            self::EVENT_REJECT => 'heroicon-o-x-circle',

            self::EVENT_REOPEN => 'heroicon-o-arrow-path',

            self::EVENT_DELETE => 'heroicon-o-trash',

            self::EVENT_KONFIRMASI => match ($this->data_baru) {
                'Disetujui' => 'heroicon-o-check-badge',
                'Ditolak' => 'heroicon-o-arrow-path',
                default => 'heroicon-o-hand-raised',
            },

            default => 'heroicon-o-clock',
        };
    }

    public function getEventColorAttribute(): string
    {
        return match ($this->event_type) {

            self::EVENT_CREATE => 'info',

            self::EVENT_ASSIGN => 'warning',

            self::EVENT_PENDING => 'pending',

            self::EVENT_CHAT => 'primary',

            self::EVENT_UPDATE => 'gray',

            self::EVENT_COMPLETE => 'success',

            self::EVENT_REJECT => 'danger',

            self::EVENT_REOPEN => 'primary',

            self::EVENT_DELETE => 'danger',

            self::EVENT_KONFIRMASI => match ($this->data_baru) {
                'Disetujui' => 'success',
                'Ditolak' => 'warning',
                default => 'info',
            },

            default => 'gray',
        };
    }

    public function getEventDescriptionAttribute(): string
    {
        return match ($this->event_type) {

            self::EVENT_UPDATE => "{$this->data_lama} → {$this->data_baru}",

            self::EVENT_DELETE => "Tiket dihapus oleh {$this->user?->name}",

            default => $this->keterangan,
        };
    }

    public function getEventGroupAttribute(): string
    {
        return match ($this->event_type) {

            self::EVENT_CREATE,
            self::EVENT_ASSIGN,
            self::EVENT_PENDING,
            self::EVENT_COMPLETE,
            self::EVENT_REJECT,
            self::EVENT_REOPEN => 'Workflow',

            self::EVENT_CHAT => 'Communication',

            self::EVENT_UPDATE => 'Data',

            self::EVENT_DELETE => 'System',

            default => 'Other',
        };
    }

    public static function getEventStatistics()
    {
        return [

            'created' => static::created()->count(),

            'assigned' => static::assign()->count(),

            'completed' => static::complete()->count(),

            'rejected' => static::reject()->count(),

            'pending' => static::pending()->count(),

            'chat' => static::where(
                'kategori_log',
                'Chat'
            )->count(),

        ];
    }

    public function getEventTypeAttribute(): string
    {
        return match ($this->kategori_log) {

            'Status' => match (true) {

                    blank($this->data_lama)
                    && $this->data_baru === 'Open' => self::EVENT_CREATE,

                    $this->data_lama === 'Open'
                    && $this->data_baru === 'In Progress' => self::EVENT_ASSIGN,

                    $this->data_lama === 'In Progress'
                    && $this->data_baru === 'Close'
                    && str_contains($this->keterangan ?? '', '[SELESAI]') => self::EVENT_COMPLETE,

                    $this->data_lama === 'In Progress'
                    && $this->data_baru === 'Close'
                    && str_contains($this->keterangan ?? '', '[DITOLAK]') => self::EVENT_REJECT,

                    $this->data_lama === 'Close'
                    && $this->data_baru === 'In Progress' => self::EVENT_REOPEN,

                    default => self::EVENT_STATUS,
                },

            'Pending' => self::EVENT_PENDING,

            'Chat' => self::EVENT_CHAT,

            'Update Data' => self::EVENT_UPDATE,

            'Delete Data' => self::EVENT_DELETE,

            'Konfirmasi' => self::EVENT_KONFIRMASI,

            default => self::EVENT_SYSTEM,
        };
    }

    public function getWorkflowStepAttribute(): int
    {
        return match ($this->event_type) {

            self::EVENT_CREATE => 1,

            self::EVENT_ASSIGN => 2,

            self::EVENT_PENDING => 3,

            self::EVENT_COMPLETE => 4,

            self::EVENT_REJECT => 4,

            self::EVENT_REOPEN => 5,

            default => 0,
        };
    }

    public static function technicianPerformance(
        int $userId
    ) {
        return static::where(
            'user_id',
            $userId
        )

            ->selectRaw("

            COUNT(*) total,

            SUM(
                kategori_log='Chat'
            ) chat,

            SUM(
                kategori_log='Update Data'
            ) update_data

        ")

            ->first();
    }

    public function getSummaryAttribute()
    {
        return match ($this->event_type) {

            self::EVENT_CREATE => "Tiket {$this->tiket?->kode_tiket} dibuat.",

            self::EVENT_ASSIGN => "{$this->user?->name} mulai menangani tiket.",

            self::EVENT_COMPLETE => 'Perbaikan selesai.',

            self::EVENT_REJECT => 'Perbaikan ditolak.',

            self::EVENT_CHAT => $this->keterangan,

            default => $this->keterangan,
        };
    }

    public function getHumanActivityAttribute(): string
    {
        return match ($this->event_type) {

            'CREATE' => "{$this->user?->name} membuat tiket.",

            'ASSIGN' => "{$this->user?->name} mengambil tiket.",

            'CHAT' => "{$this->user?->name} mengirim pesan.",

            'UPDATE' => "{$this->user?->name} memperbarui data.",

            'PENDING' => "{$this->user?->name} menunda pengerjaan.",

            'COMPLETE' => "{$this->user?->name} menyelesaikan tiket.",

            'REJECT' => "{$this->user?->name} menolak tiket.",

            'DELETE' => "{$this->user?->name} menghapus tiket.",

            default => "{$this->user?->name} melakukan aktivitas.",
        };
    }

    public function getHumanTimeAttribute(): string
    {
        return $this->created_at?->diffForHumans() ?? '-';
    }

    public function getWorkingMinutesAttribute()
    {
        if (
            !$this->waktu_mulai ||
            !$this->waktu_selesai
        ) {
            return null;
        }

        return $this->waktu_mulai
            ->diffInMinutes(
                $this->waktu_selesai
            );
    }

    public function getResponseMinutesAttribute()
    {
        $created = $this->logs()
            ->created()
            ->first();
        $assigned = $this->logs()
            ->assign()
            ->first();
        if (
            !$created ||
            !$assigned
        ) {
            return null;
        }

        return $created->created_at
            ->diffInMinutes(
                $assigned->created_at
            );
    }

    public function getSlaStatusAttribute()
    {
        if ($this->response_minutes <= 15) {
            return 'Good';
        }

        if ($this->response_minutes <= 30) {
            return 'Warning';
        }

        return 'Overdue';
    }

    public function getScoreAttribute()
    {
        return match ($this->event_type) {

            self::EVENT_CREATE => 10,
            self::EVENT_ASSIGN => 15,
            self::EVENT_PENDING => 5,
            self::EVENT_CHAT => 1,
            self::EVENT_UPDATE => 2,
            self::EVENT_COMPLETE => 20,
            self::EVENT_REJECT => -10,
            default => 0,

        };
    }
}
