<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function view(User $user, Category $category): bool
    {
        return $user->company_id === $category->company_id;
    }

    public function create(User $user): bool
    {
        return $user->canManage();
    }

    public function update(User $user, Category $category): bool
    {
        return $user->company_id === $category->company_id && $user->canManage();
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->company_id === $category->company_id && $user->canManage();
    }
}
