<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'customer_id',
        'customer_address_id',
        'delivery_driver_id',
        'coupon_id',
        'code',
        'order_type',
        'status',
        'subtotal',
        'discount_amount',
        'delivery_fee',
        'total',
        'payment_method',
        'payment_status',
        'notes',
        'change_for',
        'confirmed_at',
        'dispatched_at',
        'delivered_at',
        'canceled_at',
    ];

    protected $casts = [
        'order_type'      => OrderType::class,
        'status'          => OrderStatus::class,
        'payment_method'  => PaymentMethod::class,
        'payment_status'  => PaymentStatus::class,
        'subtotal'        => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'delivery_fee'    => 'decimal:2',
        'total'           => 'decimal:2',
        'change_for'      => 'decimal:2',
        'confirmed_at'    => 'datetime',
        'dispatched_at'   => 'datetime',
        'delivered_at'    => 'datetime',
        'canceled_at'     => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(CustomerAddress::class, 'customer_address_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(DeliveryDriver::class, 'delivery_driver_id');
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function isCanceled(): bool
    {
        return $this->status === OrderStatus::Canceled;
    }

    public function isDelivery(): bool
    {
        return $this->order_type === OrderType::Delivery;
    }

    public function canTransitionTo(OrderStatus $newStatus): bool
    {
        return in_array($newStatus, $this->status->validNextStatuses());
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeByStatus($query, OrderStatus $status)
    {
        return $query->where('status', $status->value);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }
}
