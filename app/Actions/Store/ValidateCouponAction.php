<?php

namespace App\Actions\Store;

use App\Enums\CouponType;
use App\Models\Coupon;

class ValidateCouponAction
{
    public function execute(int $companyId, string $code, float $subtotal): array
    {
        $coupon = Coupon::where('company_id', $companyId)
            ->where('code', strtoupper($code))
            ->first();

        if (! $coupon) {
            return ['valid' => false, 'message' => 'Coupon not found.'];
        }

        if (! $coupon->isValid($subtotal)) {
            if (! $coupon->is_active) {
                return ['valid' => false, 'message' => 'This coupon is inactive.'];
            }
            if ($coupon->expires_at?->isPast()) {
                return ['valid' => false, 'message' => 'This coupon has expired.'];
            }
            if ($coupon->max_uses !== null && $coupon->uses_count >= $coupon->max_uses) {
                return ['valid' => false, 'message' => 'This coupon has reached its usage limit.'];
            }
            if ($coupon->min_order_amount !== null && $subtotal < $coupon->min_order_amount) {
                return [
                    'valid'   => false,
                    'message' => "Minimum order of R$ {$coupon->min_order_amount} required for this coupon.",
                ];
            }
            return ['valid' => false, 'message' => 'Invalid coupon.'];
        }

        $discount = $coupon->calculateDiscount($subtotal);

        return [
            'valid'    => true,
            'coupon'   => [
                'id'       => $coupon->id,
                'code'     => $coupon->code,
                'type'     => $coupon->type->value,
                'value'    => (float) $coupon->value,
                'discount' => $discount,
            ],
        ];
    }
}
