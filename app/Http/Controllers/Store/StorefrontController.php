<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublicCategoryResource;
use App\Http\Resources\PublicCompanyResource;
use App\Models\Company;
use Inertia\Inertia;
use Inertia\Response;

class StorefrontController extends Controller
{
    public function home(string $companySlug): Response
    {
        $company = Company::where('slug', $companySlug)->where('is_active', true)->firstOrFail();

        $categories = $company->categories()
            ->where('is_active', true)
            ->with(['activeProducts'])
            ->orderBy('sort_order')
            ->get();

        $deliveryZones = $company->deliveryZones()
            ->where('is_active', true)
            ->select(['id', 'name', 'neighborhoods', 'delivery_fee', 'estimated_time'])
            ->get();

        return Inertia::render('Store/Home', [
            'company'       => new PublicCompanyResource($company),
            'categories'    => PublicCategoryResource::collection($categories)->resolve(),
            'deliveryZones' => $deliveryZones,
        ]);
    }

    public function cart(string $companySlug): Response
    {
        $company = Company::where('slug', $companySlug)->where('is_active', true)->firstOrFail();

        return Inertia::render('Store/Cart', [
            'company' => new PublicCompanyResource($company),
        ]);
    }

    public function checkout(string $companySlug): Response
    {
        $company = Company::where('slug', $companySlug)->where('is_active', true)->firstOrFail();

        $deliveryZones = $company->deliveryZones()
            ->where('is_active', true)
            ->select(['id', 'name', 'neighborhoods', 'delivery_fee', 'estimated_time'])
            ->get();

        return Inertia::render('Store/Checkout', [
            'company'       => new PublicCompanyResource($company),
            'deliveryZones' => $deliveryZones,
        ]);
    }

    public function success(string $companySlug, string $orderCode): Response
    {
        $company = Company::where('slug', $companySlug)->where('is_active', true)->firstOrFail();

        return Inertia::render('Store/Success', [
            'company'   => new PublicCompanyResource($company),
            'orderCode' => $orderCode,
        ]);
    }
}
