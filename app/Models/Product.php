<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_code',
        'name',
        'photo',
        'available_quantity',
        'damaged_quantity',
        'minimum_stock',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'available_quantity' => 'integer',
            'damaged_quantity' => 'integer',
            'minimum_stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function entryItems(): HasMany
    {
        return $this->hasMany(ProductEntryItem::class);
    }

    public function exitItems(): HasMany
    {
        return $this->hasMany(ProductExitItem::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(ProductHistory::class);
    }

    public function stockAlerts(): HasMany
    {
        return $this->hasMany(StockAlert::class);
    }

    public function getTotalQuantityAttribute(): int
    {
        return (int) $this->available_quantity + (int) $this->damaged_quantity;
    }

    public function getStockStatusAttribute(): string
    {
        if ((int) $this->available_quantity <= 0) {
            return 'out';
        }
        if ((int) $this->available_quantity <= (int) $this->minimum_stock) {
            return 'low';
        }
        if ((int) $this->damaged_quantity > 0) {
            return 'damaged';
        }

        return 'ok';
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('available_quantity', '<=', 'minimum_stock');
    }

    public function scopeWithDamaged(Builder $query): Builder
    {
        return $query->where('damaged_quantity', '>', 0);
    }
}
