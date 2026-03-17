<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Company;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class CategoryService
{
    public function list(Company $company, array $filters = []): LengthAwarePaginator
    {
        $query = Category::forCompany($company->id)
            ->when(isset($filters['search']), fn ($q) => $q->where('name', 'like', "%{$filters['search']}%"))
            ->when(isset($filters['active']), fn ($q) => $q->where('is_active', $filters['active']))
            ->orderBy($filters['sort'] ?? 'sort_order', $filters['direction'] ?? 'asc');

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function create(Company $company, array $data): Category
    {
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $data['image_path'] = $data['image']->store("companies/{$company->id}/categories", 'public');
            unset($data['image']);
        }

        return $company->categories()->create($data);
    }

    public function update(Category $category, array $data): Category
    {
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            if ($category->image_path) {
                Storage::disk('public')->delete($category->image_path);
            }
            $data['image_path'] = $data['image']->store("companies/{$category->company_id}/categories", 'public');
            unset($data['image']);
        }

        $category->update($data);

        return $category->fresh();
    }

    public function delete(Category $category): void
    {
        if ($category->image_path) {
            Storage::disk('public')->delete($category->image_path);
        }

        $category->delete();
    }
}
