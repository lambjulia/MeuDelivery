<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'description' => $this->description,
            'image_url'   => $this->image_path ? asset('storage/' . $this->image_path) : null,
            'sort_order'  => $this->sort_order,
            'products'    => PublicProductResource::collection($this->whenLoaded('activeProducts')),
        ];
    }
}
