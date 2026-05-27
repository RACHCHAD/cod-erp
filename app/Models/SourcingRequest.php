<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SourcingRequest extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'product_name', 'supplier_link', 'supplier_id',
        'product_cost', 'shipping_cost', 'estimated_selling_price',
        'target_countries', 'status',
        'requested_by', 'reviewed_by', 'approved_by',
        'approved_at', 'notes',
    ];

    protected $casts = [
        'product_cost' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'estimated_selling_price' => 'decimal:2',
        'target_countries' => 'array',
        'approved_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'product_cost', 'shipping_cost', 'estimated_selling_price'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getLandedCostAttribute(): float
    {
        return (float) $this->product_cost + (float) $this->shipping_cost;
    }

    public function getEstimatedMarginAttribute(): float
    {
        $price = (float) $this->estimated_selling_price;
        if ($price <= 0) {
            return 0;
        }

        return round((($price - $this->landed_cost) / $price) * 100, 2);
    }
}
