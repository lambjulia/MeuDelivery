<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerAddress extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'company_id',
        'label',
        'zip_code',
        'street',
        'number',
        'complement',
        'district',
        'city',
        'state',
        'reference',
        'latitude',
        'longitude',
        'is_default',
    ];

    protected $casts = [
        'latitude'   => 'float',
        'longitude'  => 'float',
        'is_default' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->street . ', ' . $this->number,
            $this->complement,
            $this->district,
            $this->city . ' - ' . $this->state,
            $this->zip_code,
        ]);

        return implode(', ', $parts);
    }
}
