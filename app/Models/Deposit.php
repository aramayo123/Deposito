<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deposit extends Model
{
    protected $fillable = ['name'];

    public function exits(): HasMany
    {
        return $this->hasMany(ProductExit::class);
    }
}
