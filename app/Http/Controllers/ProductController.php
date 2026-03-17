<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $productService)
    {
    }

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Product::class);

        $company    = $request->user()->company;
        $products   = $this->productService->list($company, $request->only(['search', 'category_id', 'active', 'sort', 'direction', 'per_page']));
        $categories = Category::forCompany($company->id)->active()->orderBy('sort_order')->get(['id', 'name']);

        return Inertia::render('Products/Index', [
            'products'   => $products,
            'categories' => $categories,
            'filters'    => $request->only(['search', 'category_id', 'active']),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Product::class);

        $categories = Category::forCompany($request->user()->company_id)->active()->orderBy('sort_order')->get(['id', 'name']);

        return Inertia::render('Products/Form', [
            'categories' => $categories,
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $this->productService->create($request->user()->company, $request->validated());

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function edit(Request $request, Product $product): Response
    {
        $this->authorize('update', $product);

        $categories = Category::forCompany($request->user()->company_id)->active()->orderBy('sort_order')->get(['id', 'name']);

        return Inertia::render('Products/Form', [
            'product'    => $product->load('optionGroups.options'),
            'categories' => $categories,
        ]);
    }

    public function update(StoreProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $this->productService->update($product, $request->validated());

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $this->productService->delete($product);

        return back()->with('success', 'Product deleted successfully.');
    }
}
