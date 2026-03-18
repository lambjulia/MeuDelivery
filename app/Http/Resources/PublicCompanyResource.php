<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicCompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                       => $this->id,
            'name'                     => $this->trade_name ?? $this->name,
            'slug'                     => $this->slug,
            'phone'                    => $this->phone,
            'email'                    => $this->email,
            'logo_url'                 => $this->logo_path ? asset('storage/' . $this->logo_path) : null,
            'banner_url'               => $this->banner_path ? asset('storage/' . $this->banner_path) : null,
            'about'                    => $this->about,
            'primary_color'            => $this->primary_color ?? '#F97316',
            'address'                  => [
                'street'     => $this->street,
                'number'     => $this->number,
                'complement' => $this->complement,
                'district'   => $this->district,
                'city'       => $this->city,
                'state'      => $this->state,
                'zip_code'   => $this->zip_code,
            ],
            'business_hours'           => $this->business_hours,
            'is_open'                  => $this->isOpen(),
            'default_delivery_fee'     => (float) $this->default_delivery_fee,
            'min_order_amount'         => (int) $this->min_order_amount,
            'pickup_enabled'           => $this->pickup_enabled,
            'accepted_payment_methods' => $this->accepted_payment_methods ?? [],
            'default_locale'           => $this->default_locale ?? 'pt_BR',
        ];
    }
}
