<?php

namespace App\Models\Products;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    protected $guarded = [];

    protected $casts = [
        'buying_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'is_active' => 'boolean',
        'current_stock' => 'integer',
    ];

    // Relationships
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ProductCategory::class)->using(CategoryProduct::class)->withTimestamps();
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage(): HasMany
    {
        return $this->images()->where('is_primary', true);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('current_stock', '>', 0);
    }

    public function scopeLowStock($query, $threshold = 10)
    {
        return $query->where('current_stock', '>', 0)
                     ->where('current_stock', '<=', $threshold);
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('current_stock', '<=', 0);
    }

    public function scopeSearch($query, $searchTerm)
    {
        return $query->where(function ($q) use ($searchTerm) {
            $q->where('name', 'LIKE', "%{$searchTerm}%")
              ->orWhere('sku', 'LIKE', "%{$searchTerm}%")
              ->orWhere('barcode', $searchTerm);
        });
    }

    // Methods
    public function calculateProfitMargin(): ?float
    {
        if (!$this->buying_price) {
            return null;
        }

        if ($this->buying_price == 0) {
            return 100; // Infinite profit if buying price is 0
        }

        $profit = $this->selling_price - $this->buying_price;
        return round(($profit / $this->buying_price) * 100, 2);
    }

    public function calculateProfitPerUnit(): ?float
    {
        if (!$this->buying_price) {
            return null;
        }

        return round($this->selling_price - $this->buying_price, 2);
    }

    public function getStockValue(): ?float
    {
        if (!$this->buying_price) {
            return null;
        }

        return round($this->current_stock * $this->buying_price, 2);
    }

    public function updateStock(int $quantity): bool
    {
        if ($this->current_stock + $quantity < 0) {
            return false; // Prevent negative stock if not allowed
        }

        $this->current_stock += $quantity;
        return $this->save();
    }

    public function isInStock(): bool
    {
        return $this->current_stock > 0;
    }

    public function getCategoryNames(): string
    {
        return $this->categories->pluck('name')->join(', ');
    }

    // Barcode generation helper
    public static function generateBarcode(): string
    {
        do {
            $barcode = 'PROD' . str_pad(mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT);
        } while (self::where('barcode', $barcode)->exists());

        return $barcode;
    }

    // Bulk operations
    public static function updateStockBulk(array $productUpdates): void
    {
        foreach ($productUpdates as $update) {
            DB::table('products')
                ->where('id', $update['product_id'])
                ->update([
                    'current_stock' => DB::raw("current_stock + {$update['quantity']}"),
                    'updated_at' => now(),
                ]);
        }
    }
}
