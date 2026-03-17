<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Category;
use App\Models\Customer;
use App\Models\DeliveryDriver;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService)
    {
    }

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Order::class);

        $orders = $this->orderService->list(
            $request->user()->company,
            $request->only(['status', 'search', 'date_from', 'date_to', 'per_page'])
        );

        return Inertia::render('Orders/Index', [
            'orders'   => $orders,
            'filters'  => $request->only(['status', 'search', 'date_from', 'date_to']),
            'statuses' => collect(OrderStatus::cases())->map(fn ($s) => ['value' => $s->value, 'label' => $s->label(), 'color' => $s->color()]),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Order::class);

        $company       = $request->user()->company;
        $products      = \App\Models\Product::forCompany($company->id)->active()->with(['category', 'optionGroups.activeOptions'])->orderBy('name')->get();
        $deliveryZones = \App\Models\DeliveryZone::where('company_id', $company->id)->where('is_active', true)->get();

        return Inertia::render('Orders/Create', [
            'products'      => $products,
            'deliveryZones' => $deliveryZones,
        ]);
    }

    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $order = $this->orderService->create(
            $request->user()->company,
            $request->user(),
            $request->validated()
        );

        return redirect()->route('orders.show', $order)->with('success', "Order #{$order->code} created successfully.");
    }

    public function show(Order $order): Response
    {
        $this->authorize('view', $order);

        $drivers = DeliveryDriver::forCompany($order->company_id)->available()->with('user')->get();

        return Inertia::render('Orders/Show', [
            'order'   => $order->load(['customer', 'deliveryAddress', 'driver', 'items.options', 'statusHistories.changedBy', 'coupon']),
            'drivers' => $drivers,
        ]);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $this->authorize('update', $order);

        $newStatus = OrderStatus::from($request->validated('status'));
        $this->orderService->updateStatus($order, $newStatus, $request->user(), $request->validated('note'));

        return back()->with('success', 'Order status updated.');
    }

    public function assignDriver(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('assignDriver', $order);

        $request->validate(['driver_id' => ['required', 'exists:delivery_drivers,id']]);

        $driver = DeliveryDriver::findOrFail($request->driver_id);
        $this->orderService->assignDriver($order, $driver, $request->user());

        return back()->with('success', 'Driver assigned successfully.');
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('cancel', $order);

        $request->validate(['reason' => ['nullable', 'string', 'max:500']]);

        $this->orderService->cancel($order, $request->user(), $request->input('reason'));

        return back()->with('success', 'Order canceled.');
    }

    public function dispatchBoard(Request $request): Response
    {
        $this->authorize('viewAny', Order::class);

        $company = $request->user()->company;

        $boardOrders = Order::forCompany($company->id)
            ->whereNotIn('status', [OrderStatus::Delivered->value, OrderStatus::Canceled->value])
            ->with(['customer', 'driver', 'items'])
            ->orderBy('created_at', 'asc')
            ->get()
            ->groupBy(fn ($order) => $order->status->value);

        return Inertia::render('Orders/DispatchBoard', [
            'board'   => $boardOrders,
            'statuses'=> collect(OrderStatus::cases())
                ->filter(fn ($s) => ! in_array($s, [OrderStatus::Delivered, OrderStatus::Canceled]))
                ->map(fn ($s) => ['value' => $s->value, 'label' => $s->label(), 'color' => $s->color()]),
            'drivers' => DeliveryDriver::forCompany($company->id)->available()->get(['id', 'name']),
        ]);
    }
}
