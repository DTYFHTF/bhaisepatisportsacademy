<?php

namespace App\Models;

use App\Enums\StaffRole;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => StaffRole::class,
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active;
    }

    /**
     * Role hierarchy check: front_desk < accountant < manager < super_admin.
     */
    public function isAtLeast(StaffRole $role): bool
    {
        $rank = [
            StaffRole::FrontDesk->value => 0,
            StaffRole::Accountant->value => 1,
            StaffRole::Manager->value => 2,
            StaffRole::SuperAdmin->value => 3,
        ];

        return $rank[$this->role->value] >= $rank[$role->value];
    }
}
