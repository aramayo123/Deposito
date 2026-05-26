<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductEntryItem extends Model
{
    protected $fillable = [
        'product_entry_id',
        'product_id',
        'quantity_received',
        'quantity_damaged',
        'damage_notes',
    ];

    public function entry(): BelongsTo
    {
        return $this->belongsTo(ProductEntry::class, 'product_entry_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
