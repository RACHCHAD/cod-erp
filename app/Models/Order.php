<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Order extends Model
{
    use HasFactory, LogsActivity;

    public const STATUS_PENDING = 'pending';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_SHIPPED = 'shipped';

    public const STATUS_DELIVERED = 'delivered';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_RETURNED = 'returned';

    public const STATUS_REFUNDED = 'refunded';

    protected $fillable = [
        'order_number', 'lead_id', 'country_id', 'agent_id', 'warehouse_id',
        'status', 'subtotal', 'delivery_cost', 'refund_amount', 'total',
        'product_cost', 'profit',
        'confirmed_at', 'shipped_at', 'delivered_at', 'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'delivery_cost' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'product_cost' => 'decimal:2',
        'profit' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'total', 'profit', 'delivery_cost', 'refund_amount'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (! $order->order_number) {
                $order->order_number = 'ORD-'.now()->format('Ymd').'-'.strtoupper(Str::random(5));
            }
        });
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function recalculate(): void
    {
        $subtotal = (float) $this->items()->sum(\DB::raw('quantity * unit_price'));
        $productCost = (float) $this->items()->sum(\DB::raw('quantity * unit_cost'));
        $delivery = (float) $this->delivery_cost;
        $refund = (float) $this->refund_amount;
        $this->subtotal = $subtotal;
        $this->product_cost = $productCost;
        $this->total = max(0, $subtotal - $refund);
        $this->profit = $this->total - $productCost - $delivery;
    }
}
