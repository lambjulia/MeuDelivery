<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductOptionGroup;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    public function list(Company $company, array $filters = []): LengthAwarePaginator
    {
        $query = Product::forCompany($company->id)
            ->with(['category'])
            ->when(isset($filters['search']), fn ($q) => $q->search($filters['search']))
            ->when(isset($filters['category_id']), fn ($q) => $q->where('category_id', $filters['category_id']))
            ->when(isset($filters['active']), fn ($q) => $q->where('is_active', $filters['active']))
            ->orderBy($filters['sort'] ?? 'sort_order', $filters['direction'] ?? 'asc');

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function create(Company $company, array $data): Product
    {
        return DB::transaction(function () use ($company, $data) {
            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                $data['image_path'] = $data['image']->store("companies/{$company->id}/products", 'public');
                unset($data['image']);
            }

            $optionGroups = $data['option_groups'] ?? [];
            unset($data['option_groups']);

            $product = $company->products()->create($data);

            foreach ($optionGroups as $groupData) {
                $this->createOptionGroup($product, $groupData);
            }

            return $product->load('optionGroups.options');
        });
    }

    public function update(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                if ($product->image_path) {
                    Storage::disk('public')->delete($product->image_path);
                }
                $data['image_path'] = $data['image']->store("companies/{$product->company_id}/products", 'public');
                unset($data['image']);
            }

            unset($data['option_groups']);

            $product->update($data);

            return $product->fresh()->load('optionGroups.options');
        });
    }

    public function delete(Product $product): void
    {
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();
    }

    public function createOptionGroup(Product $product, array $data): ProductOptionGroup
    {
        $options = $data['options'] ?? [];
        unset($data['options']);

        $group = $product->optionGroups()->create($data);

        foreach ($options as $optionData) {
            $group->options()->create($optionData);
        }

        return $group->load('options');
    }

    public function updateOptionGroup(ProductOptionGroup $group, array $data): ProductOptionGroup
    {
        $options = $data['options'] ?? null;
        unset($data['options']);

        $group->update($data);

        if ($options !== null) {
            // Sync options: update existing, create new, delete removed
            $existingIds = $group->options()->pluck('id')->toArray();
            $incomingIds = array_filter(array_column($options, 'id'));
            $toDelete    = array_diff($existingIds, $incomingIds);

            if ($toDelete) {
                ProductOption::whereIn('id', $toDelete)->delete();
            }

            foreach ($options as $optionData) {
                if (isset($optionData['id'])) {
                    ProductOption::find($optionData['id'])?->update($optionData);
                } else {
                    $group->options()->create($optionData);
                }
            }
        }

        return $group->fresh()->load('options');
    }

    public function deleteOptionGroup(ProductOptionGroup $group): void
    {
        $group->options()->delete();
        $group->delete();
    }
}
