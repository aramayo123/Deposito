<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class ProductEntry extends Model
{
    protected $fillable = [
        'entry_code',
        'entry_date',
        'entry_time',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ProductEntry $entry) {
            if (empty($entry->entry_code)) {
                $year = now()->year;
                $prefix = sprintf('ENT-%d-', $year);
                $last = static::query()
                    ->where('entry_code', 'like', $prefix.'%')
                    ->orderByDesc('id')
                    ->value('entry_code');
                $seq = 1;
                if ($last && preg_match('/^'.preg_quote($prefix, '/').'(\d+)$/', $last, $m)) {
                    $seq = (int) $m[1] + 1;
                }
                $entry->entry_code = sprintf('ENT-%d-%04d', $year, $seq);
            }
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProductEntryItem::class);
    }

    public function products(): HasManyThrough
    {
        return $this->hasManyThrough(
            Product::class,
            ProductEntryItem::class,
            'product_entry_id',
            'id',
            'id',
            'product_id'
        );
    }
}
