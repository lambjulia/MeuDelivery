<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_option_group_id',
        'name',
        'description',
        'additional_price',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'additional_price' => 'decimal:2',
        'is_active'        => 'boolean',
        'sort_order'       => 'integer',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(ProductOptionGroup::class, 'product_option_group_id');
    }
}
