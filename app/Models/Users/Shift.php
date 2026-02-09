<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Shift extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
