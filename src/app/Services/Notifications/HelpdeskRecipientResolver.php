<?php

namespace App\Services\Notifications;

use App\Models\Modules\Pengajuan\Models\PengajuanBarang;
use App\Models\Modules\Perbaikan\Models\TiketPerbaikan;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Menentukan penerima notifikasi berdasarkan modul dan aktivitas.
 * Teknisi hanya menerima notifikasi modul perbaikan, bukan pengajuan barang.
 */
class HelpdeskRecipientResolver
{
    /** @var array<string, list<string>> */
    protected const MODULE_STAFF_ROLES = [
        'perbaikan' => ['admin', 'teknisi', 'super_admin'],
        'pengajuan' => ['admin', 'super_admin'],
    ];

    public function resolve(
        string $module,
        int|string $referenceId,
        ?int $actorId = null,
        string $activity = 'created',
        array $data = [],
    ): Collection {
        if (! isset(self::MODULE_STAFF_ROLES[$module])) {
            return collect();
        }

        $record = $this->resolveRecord($module, $referenceId);
        if (! $record) {
            return collect();
        }

        return match ($activity) {
            'created', 'status', 'updated' => $this->mergeRecipients(
                $this->ownerRecipients($record, $actorId),
                $this->staffRecipients($module, $actorId),
            ),
            // Pembatalan hanya perlu diketahui staf; pemilik layanan adalah
            // aktornya sehingga ia sudah tahu sendiri.
            'cancelled' => $this->staffRecipients($module, $actorId),
            // Staf menandai selesai → konfirmasi diminta ke pemilik layanan.
            'confirmation_requested' => $this->ownerRecipients($record, $actorId),
            // Pemilik layanan memutuskan konfirmasi → staf yang menangani.
            'confirmed', 'confirmation_rejected' => $this->staffRecipients($module, $actorId),
            // Staf menolak layanan → pemilik layanan menerima penolakan.
            'rejected' => $this->ownerRecipients($record, $actorId),
            'chat' => $this->resolveChatRecipients($module, $record, $actorId, $data),
            default => collect(),
        };
    }

    protected function resolveRecord(string $module, int|string $referenceId): ?Model
    {
        // withTrashed(): notifikasi bisa dibuat setelah record di-soft-delete
        // (mis. pembatalan pengajuan), record tetap dibutuhkan untuk resolver.
        return match ($module) {
            'perbaikan' => TiketPerbaikan::query()->withTrashed()->with('user')->find($referenceId),
            'pengajuan' => PengajuanBarang::query()->withTrashed()->with('user')->find($referenceId),
            default => null,
        };
    }

    protected function resolveChatRecipients(
        string $module,
        Model $record,
        ?int $actorId,
        array $data,
    ): Collection {
        $allowedStaffRoles = self::MODULE_STAFF_ROLES[$module];
        $ownerId = (int) ($record->user_id ?? 0);

        $explicitIds = collect($data['recipient_ids'] ?? [])
            ->filter(fn ($id) => is_numeric($id) && (int) $id > 0)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->reject(fn (int $id) => $actorId !== null && $id === $actorId)
            ->values();

        if ($explicitIds->isNotEmpty()) {
            return User::query()
                ->whereIn('id', $explicitIds)
                ->where(function ($query) use ($ownerId, $allowedStaffRoles) {
                    $query->whereKey($ownerId)
                        ->orWhereHas('roles', fn ($roles) => $roles->whereIn('name', $allowedStaffRoles));
                })
                ->get()
                ->unique('id')
                ->values();
        }

        $actor = $actorId ? User::query()->find($actorId) : null;
        if ($actor && (int) $actor->id === $ownerId) {
            return $this->staffRecipients($module, $actorId);
        }

        if ($ownerId > 0) {
            return $this->ownerRecipients($record, $actorId);
        }

        return collect();
    }

    protected function ownerRecipients(Model $record, ?int $actorId): Collection
    {
        if (! $record->user_id) {
            return collect();
        }

        return User::query()
            ->whereKey($record->user_id)
            ->when($actorId !== null, fn ($query) => $query->where('id', '!=', $actorId))
            ->get();
    }

    protected function staffRecipients(string $module, ?int $actorId = null): Collection
    {
        $roles = self::MODULE_STAFF_ROLES[$module] ?? [];
        if ($roles === []) {
            return collect();
        }

        return User::query()
            ->whereHas('roles', fn ($query) => $query->whereIn('name', $roles))
            ->when($actorId !== null, fn ($query) => $query->where('id', '!=', $actorId))
            ->get()
            ->unique('id')
            ->values();
    }

    protected function mergeRecipients(Collection ...$collections): Collection
    {
        return collect($collections)
            ->flatten(1)
            ->filter(fn ($user) => $user instanceof User)
            ->unique('id')
            ->values();
    }
}
