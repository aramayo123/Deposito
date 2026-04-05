<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAlert extends Model
{
    protected $fillable = [
        'product_id',
        'alert_type',
        'current_quantity',
        'minimum_stock',
        'is_read',
    ];

    protected function casts(): array
    {
        return [
            'current_quantity' => 'integer',
            'minimum_stock' => 'integer',
            'is_read' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
