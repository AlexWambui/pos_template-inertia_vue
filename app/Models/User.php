<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use App\Enums\UserRoles;
use App\Enums\UserStatuses;
use App\Concerns\HasUuid;
use App\Concerns\Users\HasCreatorAuditTrail;
use App\Models\Users\StaffProfile;
use App\Models\Users\CustomerProfile;
use App\Models\Users\SupplierProfile;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasUuid, HasCreatorAuditTrail;

    protected $guarded = [];

    protected $hidden = [
        'password', 
        'two_factor_secret', 
        'two_factor_recovery_codes', 
        'remember_token'
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
            'role' => UserRoles::class,
            'status' => UserStatuses::class,
            'last_login_at' => 'datetime',
        ];
    }

    protected $appends = [
        'role_label',
        'is_active',
        'branch'
    ];

    protected $with = [
        'staffProfile',
        'customerProfile',
        'supplierProfile',
    ];

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

    public function branch()
    {
        // Get the branch associated with the user based on their role
        // Check if user has a role that should have a branch
        if ($this->role === UserRoles::CASHIER && $this->relationLoaded('staffProfile')) {
            return $this->staffProfile->belongsTo(Branch::class, 'branch_id');
        }
        
        if ($this->role === UserRoles::CUSTOMER && $this->relationLoaded('customerProfile')) {
            return $this->customerProfile->belongsTo(Branch::class, 'branch_id');
        }
        
        if ($this->role === UserRoles::SUPPLIER && $this->relationLoaded('supplierProfile')) {
            return $this->supplierProfile->belongsTo(Branch::class, 'branch_id');
        }
        
        return null;
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

    public function isActive(): bool
    {
        return $this->status === UserStatuses::ACTIVE;
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->isActive();
    }

    public function getRoleLabelAttribute(): string
    {
        return $this->role->label();
    }

    public function getBranchAttribute()
    {
        $relation = $this->branch();
        return $relation ? $relation->get()->first() : null;
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
        // If role is empty string or null, don't filter
        if ($role === null || $role === '' || $role === 'null') {
            return $query;
        }

        // Handle numeric values
        if (is_numeric($role)) {
            return $query->where('role', (int) $role);
        }
        
        // Handle string labels (for direct label filtering)
        $roleEnum = UserRoles::tryFromLabel($role);
        if ($roleEnum) {
            return $query->where('role', $roleEnum->value);
        }
        
        return $query;
    }
}
