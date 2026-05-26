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

    public function getTotalQuantityAttribute(): float
    {
        return (float) $this->available_quantity + (float) $this->damaged_quantity;
    }

    public function getStockStatusAttribute(): string
    {
        if ((float) $this->available_quantity <= 0) {
            return 'out';
        }
        if ((float) $this->available_quantity <= (float) $this->minimum_stock) {
            return 'low';
        }
        if ((float) $this->damaged_quantity > 0) {
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
