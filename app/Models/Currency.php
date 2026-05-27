<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Currency extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'symbol', 'rate_to_usd', 'active'];

    protected $casts = [
        'rate_to_usd' => 'decimal:6',
        'active' => 'boolean',
    ];

    public function countries(): HasMany
    {
        return $this->hasMany(Country::class);
    }
}
