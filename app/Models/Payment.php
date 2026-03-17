<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'company_id',
        'payment_method',
        'status',
        'amount',
        'change_amount',
        'notes',
        'processed_at',
    ];

    protected $casts = [
        'payment_method' => PaymentMethod::class,
        'status'         => PaymentStatus::class,
        'amount'         => 'decimal:2',
        'change_amount'  => 'decimal:2',
        'processed_at'   => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
