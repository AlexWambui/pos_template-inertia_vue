<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use App\Enums\UserStatuses;
use App\Enums\UserRoles;
use Illuminate\Support\Str;
use App\Models\Users\StaffProfile;
use App\Models\Users\CustomerProfile;
use App\Models\Users\SupplierProfile;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'last_login_at' => 'datetime',
            'status' => UserStatuses::class,
            'role' => UserRoles::class,
        ];
    }

    protected $appends = [
        'role_label',
    ];

    protected $with = [
        'staffProfile',
        'customerProfile',
        'supplierProfile',
    ];

    protected static function booted()
    {
        static::creating(function ($user) {
            if (!$user->uuid) {
                $user->uuid = (string) Str::uuid();
            }
        });
    }

    public function hasRole(string $role_name): bool
    {
        // Convert string role name to enum value
        foreach (UserRoles::cases() as $role) {
            if (strtolower($role->name) === strtolower($role_name)) {
                return $this->role->value === $role->value;
            }
        }
        return false;
    }
    
    public function hasAnyRole(array $role_names): bool
    {
        foreach ($role_names as $role_name) {
            if ($this->hasRole($role_name)) {
                return true;
            }
        }

        return false;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRoles::SUPER_ADMIN;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRoles::ADMIN;
    }
    
    public function isCashier(): bool
    {
        return $this->role === UserRoles::CASHIER;
    }
    
    public function isSupplier(): bool
    {
        return $this->role === UserRoles::SUPPLIER;
    }
    
    public function isCustomer(): bool
    {
        return $this->role === UserRoles::CUSTOMER;
    }
    
    // Check if user is staff (either admin or cashier)
    public function isStaff(): bool
    {
        return in_array($this->role, [
            UserRoles::SUPER_ADMIN,
            UserRoles::ADMIN,
            UserRoles::CASHIER
        ]);
    }

    public function staffProfile()
    {
        return $this->hasOne(StaffProfile::class);
    }

    public function customerProfile()
    {
        return $this->hasOne(CustomerProfile::class);
    }

    public function supplierProfile()
    {
        return $this->hasOne(SupplierProfile::class);
    }

    public function shifts()
    {
        return $this->hasMany(\App\Models\Users\Shift::class);
    }

    // Helper Methods
    public function isActive(): bool
    {
        return $this->status === 1;
    }

    public function getRoleLabelAttribute(): string
    {
        return $this->role->label();
    }

    // Scope for specific roles
    public function scopeWhereRole($query, UserRoles $role)
    {
        return $query->where('role', $role->value);
    }
    
    public function scopeStaff($query)
    {
        return $query->whereIn('role', [
            UserRoles::SUPER_ADMIN->value,
            UserRoles::ADMIN->value,
            UserRoles::CASHIER->value
        ]);
    }
    
    public function scopeCustomers($query)
    {
        return $query->whereRole(UserRoles::CUSTOMER);
    }
    
    public function scopeSuppliers($query)
    {
        return $query->whereRole(UserRoles::SUPPLIER);
    }

    public function scopeOrderByRolePriority($query)
    {
        return $query->orderByRaw(
            "CASE
                WHEN role = ? THEN 1
                WHEN role = ? THEN 2
                WHEN role = ? THEN 3
                WHEN role = ? THEN 4
                WHEN role = ? THEN 5
                ELSE 6
            END ASC",
            [
                UserRoles::SUPER_ADMIN->value,
                UserRoles::ADMIN->value,
                UserRoles::CASHIER->value,
                UserRoles::SUPPLIER->value,
                UserRoles::CUSTOMER->value,
            ]
        )->orderBy('name');
    }

    public function scopeSearch($query, $search)
    {
        if (!$search) return $query;
        
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%");
        });
    }

    public function scopeFilterByRole($query, $role)
    {
        if ($role === null || $role === '') {
            return $query;
        }

        $role_value = is_numeric($role) ? (int)$role : $role;
        
        return $query->where('role', $role);
    }

    public function openShift()
    {
        return $this->hasOne(\App\Models\Users\Shift::class)->whereNull('closed_at');
    }

    public function hasOpenShift(): bool
    {
        return $this->openShift()->exists();
    }
}
