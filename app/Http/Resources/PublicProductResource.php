<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicProductResource extends JsonResource
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
        ];
    }
}
