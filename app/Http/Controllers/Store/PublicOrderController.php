<?php

namespace App\Http\Controllers\Store;

use App\Actions\Store\CreatePublicOrderAction;
use App\Actions\Store\ValidateCouponAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePublicOrderRequest;
use App\Http\Requests\ValidateCouponRequest;
use App\Http\Resources\PublicOrderTrackingResource;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class PublicOrderController extends Controller
{
    public function __construct(
        private readonly CreatePublicOrderAction $createOrder,
        private readonly ValidateCouponAction    $validateCoupon,
    ) {}

    public function store(string $companySlug, StorePublicOrderRequest $request): JsonResponse
    {
        $company = Company::where('slug', $companySlug)->where('is_active', true)->firstOrFail();

        try {
            $order = $this->createOrder->execute($company, $request->validated());
        } catch (\InvalidArgumentException $e) {
            throw ValidationException::withMessages(['order' => $e->getMessage()]);
        }

        return response()->json([
            'success'    => true,
            'order_code' => $order->code,
            'redirect'   => route('store.success', [$companySlug, $order->code]),
        ], 201);
    }

    public function validateCoupon(string $companySlug, ValidateCouponRequest $request): JsonResponse
    {
        $company = Company::where('slug', $companySlug)->where('is_active', true)->firstOrFail();

        $result = $this->validateCoupon->execute(
            $company->id,
            $request->input('code'),
            (float) $request->input('subtotal'),
        );

        return response()->json($result);
    }
}
