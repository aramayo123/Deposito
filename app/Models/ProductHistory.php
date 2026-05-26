<?php

namespace App\Models;

use App\Support\HistoryActionLabels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductHistory extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'product_histories';

    protected $fillable = [
        'product_id',
        'action_type',
        'reference_type',
        'reference_id',
        'description',
        'technician_name',
        'license_plate',
        'deposit_id',
        'quantity_change',
        'quantity_before',
        'quantity_after',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function deposit(): BelongsTo
    {
        return $this->belongsTo(Deposit::class);
    }

    public function getActionLabelEsAttribute(): string
    {
        return HistoryActionLabels::spanish($this->action_type);
    }

    public function getActionDisplayAttribute(): string
    {
        return HistoryActionLabels::forDisplay($this->action_type);
    }
}
