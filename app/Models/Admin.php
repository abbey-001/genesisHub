<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $guard = 'admin';

    protected $fillable = [
        'role_id',
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'is_active',
        'last_login_at',
        'last_login_ip',
        'telegram_linked_at',
        'telegram_invited_at',
        'telegram_link_token',
        'telegram_chat_id',
        'telegram_notify_orders',
        'telegram_notify_payouts',
        'telegram_notify_sellers',
        'telegram_notify_reviews',
        'telegram_notify_deliveries',
        'telegram_notify_riders',
        'telegram_notify_system',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'telegram_link_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'telegram_linked_at' => 'datetime',
        'telegram_invited_at' => 'datetime',
        'telegram_notify_orders' => 'boolean',
        'telegram_notify_payouts' => 'boolean',
        'telegram_notify_sellers' => 'boolean',
        'telegram_notify_reviews' => 'boolean',
        'telegram_notify_deliveries' => 'boolean',
        'telegram_notify_riders' => 'boolean',
        'telegram_notify_system' => 'boolean',
    ];

    // Relationships
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // Helper Methods
    public function hasRole(string $roleName): bool
    {
        return $this->role && $this->role->name === $roleName;
    }

    public function hasAnyRole(array $roles): bool
    {
        return $this->role && in_array($this->role->name, $roles);
    }

    public function hasPermission(string $permissionName): bool
    {
        if (!$this->role) {
            return false;
        }

        // Super Admin has all permissions
        if ($this->role->name === 'super_admin') {
            return true;
        }

        return $this->role->permissions()
            ->where('name', $permissionName)
            ->exists();
    }

    public function hasAnyPermission(array $permissions): bool
    {
        if (!$this->role) {
            return false;
        }

        // Super Admin has all permissions
        if ($this->role->name === 'super_admin') {
            return true;
        }

        return $this->role->permissions()
            ->whereIn('name', $permissions)
            ->exists();
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function updateLastLogin(string $ip): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip,
        ]);
    }

    // Accessors
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
    }

    public function getRoleNameAttribute(): string
    {
        return $this->role ? $this->role->display_name : 'No Role';
    }
}
