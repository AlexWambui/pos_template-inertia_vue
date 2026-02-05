<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Users\StaffProfile;

class Branch extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($branch) {
            if (!$branch->uuid) {
                $branch->uuid = (string) Str::uuid();
            }
        });
    }

    public function staffProfiles()
    {
        return $this->hasMany(StaffProfile::class);
    }
}
