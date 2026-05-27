<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name', 'flag_emoji', 'currency_id',
        'avg_delivery_cost', 'avg_confirmation_rate', 'avg_delivery_rate',
        'active', 'sort_order',
    ];

    protected $casts = [
        'avg_delivery_cost' => 'decimal:2',
        'avg_confirmation_rate' => 'decimal:2',
        'avg_delivery_rate' => 'decimal:2',
        'active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function adSpends(): HasMany
    {
        return $this->hasMany(AdSpend::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function warehouses(): HasMany
    {
        return $this->hasMany(Warehouse::class);
    }

    public function getLabelAttribute(): string
    {
        return trim(($this->flag_emoji ? $this->flag_emoji.' ' : '').$this->name);
    }
}
