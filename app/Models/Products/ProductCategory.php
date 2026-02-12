<?php

namespace App\Models\Products;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ProductCategory extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('name') && empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    // Relationships
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ProductCategory::class, 'parent_id')->orderBy('sort_order');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->using(CategoryProduct::class)->withTimestamps();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    // Methods
    public function getHierachyName(): string
    {
        $name = [];
        $category = $this;

        while ($category) {
            $names[] = $category->name;
            $category = $category->parent;
        }

        return implode(' -> ', array_reverse($names));
    }

    public function getAllChildIds(): array
    {
        $ids = [$this->id];

        foreach ($this->children as $child) {
            $ids = array_merge($ids, $child->getAllChildIds());
        }

        return $ids;
    }

    public function isDescendatOf($category_id): bool
    {
        $category = $this;

        while ($category->parent_id) {
            if ($category->parent_id == $category_id) {
                return true;
            }
            $category = $category->parent;
        }

        return false;
    }

    public function getProductsCount(): int
    {
        $category_ids = $this->getAllChildIds();

        return Product::whereHas('categories', function ($query) use ($category_ids) {
            $query->whereIn('category_id', $category_ids);
        })->count();
    }
}
