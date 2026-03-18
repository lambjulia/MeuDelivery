<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicProductDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'slug'              => $this->slug,
            'description'       => $this->description,
            'image_url'         => $this->image_path ? asset('storage/' . $this->image_path) : null,
            'base_price'        => (float) $this->base_price,
            'promotional_price' => $this->promotional_price ? (float) $this->promotional_price : null,
            'current_price'     => (float) ($this->promotional_price ?? $this->base_price),
            'preparation_time'  => $this->preparation_time,
            'category_id'       => $this->category_id,
            'option_groups'     => $this->optionGroups
                ->where('is_active', true)
                ->sortBy('sort_order')
                ->map(fn ($group) => [
                    'id'             => $group->id,
                    'name'           => $group->name,
                    'description'    => $group->description,
                    'is_required'    => $group->is_required,
                    'is_multiple'    => $group->is_multiple,
                    'min_selections' => $group->min_selections,
                    'max_selections' => $group->max_selections,
                    'options'        => $group->activeOptions->map(fn ($opt) => [
                        'id'               => $opt->id,
                        'name'             => $opt->name,
                        'description'      => $opt->description,
                        'additional_price' => (float) $opt->additional_price,
                    ])->values(),
                ])->values(),
        ];
    }
}
