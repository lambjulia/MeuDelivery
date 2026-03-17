<?php

namespace App\Http\Controllers;

use App\Models\DeliveryZone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DeliveryZoneController extends Controller
{
    public function index(Request $request): Response
    {
        $zones = DeliveryZone::forCompany($request->user()->company_id)
            ->orderBy('name')
            ->paginate(15);

        return Inertia::render('DeliveryZones/Index', [
            'zones' => $zones,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:100'],
            'neighborhoods'  => ['nullable', 'array'],
            'neighborhoods.*'=> ['string'],
            'delivery_fee'   => ['required', 'numeric', 'min:0'],
            'estimated_time' => ['nullable', 'integer', 'min:1'],
            'is_active'      => ['boolean'],
        ]);

        $data['company_id'] = $request->user()->company_id;
        DeliveryZone::create($data);

        return back()->with('success', 'Delivery zone created.');
    }

    public function update(Request $request, DeliveryZone $deliveryZone): RedirectResponse
    {
        abort_if($deliveryZone->company_id !== $request->user()->company_id, 403);

        $data = $request->validate([
            'name'           => ['string', 'max:100'],
            'neighborhoods'  => ['nullable', 'array'],
            'delivery_fee'   => ['numeric', 'min:0'],
            'estimated_time' => ['nullable', 'integer', 'min:1'],
            'is_active'      => ['boolean'],
        ]);

        $deliveryZone->update($data);

        return back()->with('success', 'Delivery zone updated.');
    }

    public function destroy(Request $request, DeliveryZone $deliveryZone): RedirectResponse
    {
        abort_if($deliveryZone->company_id !== $request->user()->company_id, 403);

        $deliveryZone->delete();

        return back()->with('success', 'Delivery zone deleted.');
    }
}
