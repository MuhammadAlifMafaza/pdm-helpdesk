<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'unit_bidang'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    use HasFactory, HasRoles, Notifiable;

    protected $guard_name = 'web';

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'unit_bidang',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /* Get the name attribute. */
    public function getFilamentName(): string
    {
        return $this->name ?: $this->email ?: 'User';
    }

    /**
     * Get the email attribute.
     */
    public function getEmailAttribute(): string
    {
        return $this->attributes['email'] ?? '';
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'admin' => $this->hasAnyRole([
                'admin',
                'teknisi',
                'super_admin',
            ]),
            'pemohon' => $this->hasRole('pemohon'),
            default => false,
        };
    }

    /**
     * Halaman utama yang berhak dibuka user sesuai perannya.
     *
     * Mengembalikan null ketika user tidak memegang peran apa pun sehingga
     * pemanggil wajib menanganinya (misalnya tetap menampilkan 403).
     */
    public function homeUrl(): ?string
    {
        if ($this->hasAnyRole(['admin', 'teknisi', 'super_admin'])) {
            return '/admin';
        }

        if ($this->hasRole('pemohon')) {
            return '/pemohon';
        }

        return null;
    }

    public function receivesBroadcastNotificationsOn(): string
    {
        return 'users.'.$this->id;
    }
}
