<?php

namespace App\Models\Products;

use Illuminate\Database\Eloquent\Model;
use App\Concerns\HasUuid;

class Product extends Model
{
    use HasUuid;

    protected $guarded = [];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->sort_order)) {
                $model->sort_order = static::max('sort_order') + 1;
            }
        });
    }

    public function category()
    {
        $this->belongsTo(ProductCategory::class);
    }
}
