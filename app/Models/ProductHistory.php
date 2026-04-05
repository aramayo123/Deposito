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
        'quantity_change',
        'quantity_before',
        'quantity_after',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'quantity_change' => 'integer',
            'quantity_before' => 'integer',
            'quantity_after' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
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
