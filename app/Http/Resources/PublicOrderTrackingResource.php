<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicOrderTrackingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'code'           => $this->code,
            'status'         => $this->status->value,
            'status_label'   => $this->status->label(),
            'order_type'     => $this->order_type->value,
            'subtotal'       => (float) $this->subtotal,
            'discount'       => (float) $this->discount_amount,
            'delivery_fee'   => (float) $this->delivery_fee,
            'total'          => (float) $this->total,
            'payment_method' => $this->payment_method->value,
            'notes'          => $this->notes,
            'items'          => $this->items->map(fn ($item) => [
                'name'       => $item->product_name,
                'quantity'   => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'total'      => (float) $item->total,
                'options'    => $item->options->map(fn ($opt) => [
                    'group'  => $opt->product_option_group_name,
                    'name'   => $opt->product_option_name,
                    'price'  => (float) $opt->additional_price,
                ])->values(),
            ])->values(),
            'address'        => $this->whenLoaded('address', fn () => $this->address ? [
                'street'     => $this->address->street,
                'number'     => $this->address->number,
                'complement' => $this->address->complement,
                'district'   => $this->address->district,
                'city'       => $this->address->city,
            ] : null),
            'history'        => $this->statusHistory->map(fn ($h) => [
                'status'     => $h->status->value,
                'label'      => $h->status->label(),
                'created_at' => $h->created_at->toIso8601String(),
            ])->values(),
            'confirmed_at'   => $this->confirmed_at?->toIso8601String(),
            'dispatched_at'  => $this->dispatched_at?->toIso8601String(),
            'delivered_at'   => $this->delivered_at?->toIso8601String(),
            'created_at'     => $this->created_at->toIso8601String(),
        ];
    }
}
