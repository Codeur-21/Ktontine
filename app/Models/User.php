<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    use HasRoles, Notifiable;

    protected $fillable = [
        'tenant_id',
        'name',
        'phone',
        'email',
        'password',
        'role',
        'is_active',
        'terms_accepted_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (User $user): void {
            abort_if($user->exists && $user->isDirty('tenant_id'), 403, 'Le rattachement d’un utilisateur ne peut pas être changé.');
        });
    }

    public function getFilamentAvatarUrl(): ?string
    {
        if ($this->avatar_path && str_starts_with($this->avatar_path, 'avatars/'.$this->id.'/')
            && Storage::disk('public')->exists($this->avatar_path)) {
            return Storage::disk('public')->url($this->avatar_path);
        }

        return asset('default-avatar.svg');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'workspace' && $this->isPlatformAdmin();
    }

    public function isPlatformAdmin(): bool
    {
        return $this->is_active && $this->tenant_id === null && $this->role === 'owner';
    }

    public function activeTenantId(): int
    {
        abort_unless(
            $this->is_active
            && $this->tenant_id !== null
            && $this->tenant()->where('is_active', true)->exists(),
            403,
            'Votre espace est indisponible ou votre accès a été désactivé.',
        );

        return (int) $this->tenant_id;
    }

    // Un utilisateur appartient à un Tenant
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
