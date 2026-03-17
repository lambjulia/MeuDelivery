<?php

namespace App\Http\Controllers;

use App\Enums\CouponType;
use App\Models\Coupon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CouponController extends Controller
{
    public function index(Request $request): Response
    {
        $coupons = Coupon::forCompany($request->user()->company_id)
            ->when($request->search, fn ($q) => $q->where('code', 'like', "%{$request->search}%"))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Coupons/Index', [
            'coupons' => $coupons,
            'filters' => $request->only(['search']),
            'types'   => collect(CouponType::cases())->map(fn ($t) => ['value' => $t->value, 'label' => $t->label()]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code'             => ['required', 'string', 'max:30'],
            'type'             => ['required', 'in:fixed,percentage'],
            'value'            => ['required', 'numeric', 'min:0.01'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'max_uses'         => ['nullable', 'integer', 'min:1'],
            'expires_at'       => ['nullable', 'date', 'after:today'],
            'is_active'        => ['boolean'],
        ]);

        $data['company_id'] = $request->user()->company_id;

        // Check code uniqueness within company
        $exists = Coupon::forCompany($data['company_id'])->where('code', $data['code'])->exists();
        if ($exists) {
            return back()->withErrors(['code' => 'This coupon code already exists.']);
        }

        Coupon::create($data);

        return back()->with('success', 'Coupon created.');
    }

    public function update(Request $request, Coupon $coupon): RedirectResponse
    {
        abort_if($coupon->company_id !== $request->user()->company_id, 403);

        $data = $request->validate([
            'value'            => ['numeric', 'min:0.01'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'max_uses'         => ['nullable', 'integer', 'min:1'],
            'expires_at'       => ['nullable', 'date'],
            'is_active'        => ['boolean'],
        ]);

        $coupon->update($data);

        return back()->with('success', 'Coupon updated.');
    }

    public function destroy(Request $request, Coupon $coupon): RedirectResponse
    {
        abort_if($coupon->company_id !== $request->user()->company_id, 403);

        $coupon->delete();

        return back()->with('success', 'Coupon deleted.');
    }
}
