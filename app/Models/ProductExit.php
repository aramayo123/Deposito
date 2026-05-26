<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductExit extends Model
{
    protected $fillable = [
        'exit_code',
        'exit_date',
        'exit_time',
        'technician_name',
        'deposit_id',
        'is_for_workshop',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'exit_date' => 'date',
            'is_for_workshop' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ProductExit $exit) {
            if (empty($exit->exit_code)) {
                $year = now()->year;
                $prefix = sprintf('SAL-%d-', $year);
                $last = static::query()
                    ->where('exit_code', 'like', $prefix.'%')
                    ->orderByDesc('id')
                    ->value('exit_code');
                $seq = 1;
                if ($last && preg_match('/^'.preg_quote($prefix, '/').'(\d+)$/', $last, $m)) {
                    $seq = (int) $m[1] + 1;
                }
                $exit->exit_code = sprintf('SAL-%d-%04d', $year, $seq);
            }
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProductExitItem::class);
    }

    public function deposit(): BelongsTo
    {
        return $this->belongsTo(Deposit::class);
    }
}
