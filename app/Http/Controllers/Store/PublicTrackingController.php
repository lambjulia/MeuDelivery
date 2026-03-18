<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublicCompanyResource;
use App\Http\Resources\PublicOrderTrackingResource;
use App\Models\Company;
use App\Models\Order;
use Inertia\Inertia;
use Inertia\Response;

class PublicTrackingController extends Controller
{
    public function show(string $companySlug, string $orderCode): Response
    {
        $company = Company::where('slug', $companySlug)->where('is_active', true)->firstOrFail();

        $order = Order::where('company_id', $company->id)
            ->where('code', $orderCode)
            ->with(['items.options', 'address', 'statusHistory'])
            ->firstOrFail();

        return Inertia::render('Store/Tracking', [
            'company' => new PublicCompanyResource($company),
            'order'   => new PublicOrderTrackingResource($order),
        ]);
    }

    public function poll(string $companySlug, string $orderCode): \Illuminate\Http\JsonResponse
    {
        $company = Company::where('slug', $companySlug)->where('is_active', true)->firstOrFail();

        $order = Order::where('company_id', $company->id)
            ->where('code', $orderCode)
            ->with(['items.options', 'address', 'statusHistory'])
            ->firstOrFail();

        return response()->json(new PublicOrderTrackingResource($order));
    }
}
