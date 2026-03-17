<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function view(User $user, Order $order): bool
    {
        return $user->company_id === $order->company_id;
    }

    public function create(User $user): bool
    {
        return $user->company_id !== null;
    }

    public function update(User $user, Order $order): bool
    {
        return $user->company_id === $order->company_id;
    }

    public function cancel(User $user, Order $order): bool
    {
        return $user->company_id === $order->company_id && $user->canManage();
    }

    public function assignDriver(User $user, Order $order): bool
    {
        return $user->company_id === $order->company_id;
    }
}
