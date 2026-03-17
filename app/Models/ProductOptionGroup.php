<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductOptionGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'description',
        'is_required',
        'min_selections',
        'max_selections',
        'is_multiple',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_required'    => 'boolean',
        'is_multiple'    => 'boolean',
        'is_active'      => 'boolean',
        'min_selections' => 'integer',
        'max_selections' => 'integer',
        'sort_order'     => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(ProductOption::class)->orderBy('sort_order');
    }

    public function activeOptions(): HasMany
    {
        return $this->hasMany(ProductOption::class)->where('is_active', true)->orderBy('sort_order');
    }
}
