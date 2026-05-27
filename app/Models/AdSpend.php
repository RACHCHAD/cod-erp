<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdSpend extends Model
{
    use HasFactory;

    protected $fillable = [
        'date', 'product_id', 'country_id', 'platform_id',
        'ad_account_id', 'user_id',
        'spend', 'leads', 'impressions', 'clicks', 'cpl', 'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'spend' => 'decimal:2',
        'leads' => 'integer',
        'impressions' => 'integer',
        'clicks' => 'integer',
        'cpl' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (AdSpend $spend) {
            $spend->cpl = $spend->leads > 0
                ? round((float) $spend->spend / $spend->leads, 2)
                : null;
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }

    public function adAccount(): BelongsTo
    {
        return $this->belongsTo(AdAccount::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function leadRecords(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function getProfitabilityAttribute(): string
    {
        $cpl = (float) ($this->cpl ?? 0);
        $target = (float) ($this->product?->selling_price ?? 0) * 0.10;
        if ($target <= 0 || $cpl <= 0) {
            return 'neutral';
        }
        if ($cpl <= $target * 0.6) {
            return 'profitable';
        }
        if ($cpl <= $target) {
            return 'average';
        }

        return 'losing';
    }
}
