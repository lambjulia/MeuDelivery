<?php

namespace App\Http\Controllers;

use App\Models\DeliveryDriver;
use App\Models\DeliveryZone;
use App\Enums\DriverStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DeliveryDriverController extends Controller
{
    public function index(Request $request): Response
    {
        $drivers = DeliveryDriver::forCompany($request->user()->company_id)
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->orderBy('name')
            ->get();

        return Inertia::render('DeliveryDrivers/Index', [
            'drivers' => $drivers,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'            => ['required', 'string', 'max:150'],
            'phone'           => ['required', 'string', 'max:20'],
            'document'        => ['nullable', 'string', 'max:20'],
            'employment_type' => ['nullable', 'string'],
            'vehicle_type'    => ['nullable', 'string'],
            'license_plate'   => ['nullable', 'string', 'max:10'],
        ]);

        $data['company_id'] = $request->user()->company_id;
        $data['status']     = DriverStatus::Offline->value;

        DeliveryDriver::create($data);

        return back()->with('success', 'Driver added successfully.');
    }

    public function update(Request $request, DeliveryDriver $deliveryDriver): RedirectResponse
    {
        abort_if($deliveryDriver->company_id !== $request->user()->company_id, 403);

        $data = $request->validate([
            'name'            => ['string', 'max:150'],
            'phone'           => ['string', 'max:20'],
            'document'        => ['nullable', 'string', 'max:20'],
            'employment_type' => ['nullable', 'string'],
            'vehicle_type'    => ['nullable', 'string'],
            'license_plate'   => ['nullable', 'string', 'max:10'],
            'status'          => ['nullable', 'in:available,busy,offline'],
            'is_active'       => ['boolean'],
        ]);

        $deliveryDriver->update($data);

        return back()->with('success', 'Driver updated successfully.');
    }

    public function destroy(Request $request, DeliveryDriver $deliveryDriver): RedirectResponse
    {
        abort_if($deliveryDriver->company_id !== $request->user()->company_id, 403);

        $deliveryDriver->delete();

        return back()->with('success', 'Driver removed successfully.');
    }
}
