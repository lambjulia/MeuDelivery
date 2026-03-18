<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'trade_name',
        'slug',
        'document',
        'phone',
        'email',
        'logo_path',
        'banner_path',
        'about',
        'primary_color',
        'zip_code',
        'street',
        'number',
        'complement',
        'district',
        'city',
        'state',
        'business_hours',
        'default_delivery_fee',
        'min_order_amount',
        'pickup_enabled',
        'accepted_payment_methods',
        'default_locale',
        'is_active',
    ];

    protected $casts = [
        'business_hours'           => 'array',
        'accepted_payment_methods' => 'array',
        'default_delivery_fee'     => 'decimal:2',
        'min_order_amount'         => 'integer',
        'pickup_enabled'           => 'boolean',
        'is_active'                => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $company) {
            if (empty($company->slug)) {
                $company->slug = Str::slug($company->trade_name ?? $company->name);
            }
        });
    }

    /** Check if the store is currently open based on business_hours JSON. */
    public function isOpen(): bool
    {
        $hours = $this->business_hours;
        if (empty($hours)) {
            return true;
        }

        $now      = now();
        $dayMap   = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $dayName  = $dayMap[$now->dayOfWeek];
        $dayHours = $hours[$dayName] ?? null;

        if (! $dayHours || ! ($dayHours['open'] ?? false)) {
            return false;
        }

        $open  = $dayHours['from'] ?? '00:00';
        $close = $dayHours['to']   ?? '23:59';

        return $now->format('H:i') >= $open && $now->format('H:i') <= $close;
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function deliveryZones(): HasMany
    {
        return $this->hasMany(DeliveryZone::class);
    }

    public function deliveryDrivers(): HasMany
    {
        return $this->hasMany(DeliveryDriver::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function coupons(): HasMany
    {
        return $this->hasMany(Coupon::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}

