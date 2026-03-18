<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublicCompanyResource;
use App\Http\Resources\PublicProductDetailResource;
use App\Models\Company;
use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class PublicProductController extends Controller
{
    public function show(string $companySlug, string $productSlug): Response
    {
        $company = Company::where('slug', $companySlug)->where('is_active', true)->firstOrFail();

        $product = Product::where('company_id', $company->id)
            ->where('slug', $productSlug)
            ->where('is_active', true)
            ->with(['optionGroups.activeOptions'])
            ->firstOrFail();

        return Inertia::render('Store/ProductDetail', [
            'company' => new PublicCompanyResource($company),
            'product' => new PublicProductDetailResource($product),
        ]);
    }
}
