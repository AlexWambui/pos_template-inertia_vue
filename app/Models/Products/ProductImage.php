<?php

namespace App\Models\Products;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Scopes
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeSorted($query)
    {
        return $query->orderBy('sort_order');
    }

    // Methods
    public function getUrlAttribute(): ?string
    {
        if (!$this->path) {
            return null;
        }

        return Storage::url($this->path);
    }

    public function makePrimary(): bool
    {
        // Remove primary status from other images of this product
        $this->product->images()->where('is_primary', true)->update(['is_primary' => false]);
        
        $this->is_primary = true;
        return $this->save();
    }

    public function deleteWithFile(): bool
    {
        if ($this->path && Storage::exists($this->path)) {
            Storage::delete($this->path);
        }

        return $this->delete();
    }
}
