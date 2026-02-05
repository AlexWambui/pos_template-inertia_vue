<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class CustomerProfile extends Model
{
    protected $guarded = [];

    protected $casts = [
        'loyalty_points' => 'integer',
        'credit_limit' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
