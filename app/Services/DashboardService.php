<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Order;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getMetrics(Company $company, array $filters = []): array
    {
        $period     = $filters['period'] ?? 'today';
        $cacheKey   = "dashboard.{$company->id}.{$period}";
        $cacheTtl   = $period === 'today' ? 300 : 3600; // 5m for today, 1h otherwise

        return Cache::remember($cacheKey, $cacheTtl, fn () => $this->computeMetrics($company, $period, $filters));
    }

    private function computeMetrics(Company $company, string $period, array $filters): array
    {
        [$dateFrom, $dateTo] = $this->resolvePeriod($period, $filters);

        $ordersQuery = Order::forCompany($company->id)
            ->whereBetween('created_at', [$dateFrom, $dateTo]);

        $totalOrders     = $ordersQuery->count();
        $deliveredOrders = (clone $ordersQuery)->where('status', OrderStatus::Delivered->value)->count();
        $canceledOrders  = (clone $ordersQuery)->where('status', OrderStatus::Canceled->value)->count();
        $activeOrders    = (clone $ordersQuery)->whereNotIn('status', [
            OrderStatus::Delivered->value, OrderStatus::Canceled->value,
        ])->count();

        $revenue    = (clone $ordersQuery)->where('status', OrderStatus::Delivered->value)->sum('total');
        $avgTicket  = $deliveredOrders > 0 ? $revenue / $deliveredOrders : 0;

        // Top products
        $topProducts = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.company_id', $company->id)
            ->where('orders.status', OrderStatus::Delivered->value)
            ->whereBetween('orders.created_at', [$dateFrom, $dateTo])
            ->select('order_items.product_name', DB::raw('SUM(order_items.quantity) as total_qty'), DB::raw('SUM(order_items.total) as total_revenue'))
            ->groupBy('order_items.product_name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // Payment methods distribution
        $paymentMethods = (clone $ordersQuery)
            ->where('status', OrderStatus::Delivered->value)
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as total'))
            ->groupBy('payment_method')
            ->get();

        // Top drivers
        $topDrivers = DB::table('orders')
            ->join('delivery_drivers', 'delivery_drivers.id', '=', 'orders.delivery_driver_id')
            ->where('orders.company_id', $company->id)
            ->where('orders.status', OrderStatus::Delivered->value)
            ->whereBetween('orders.created_at', [$dateFrom, $dateTo])
            ->select('delivery_drivers.name', DB::raw('COUNT(*) as deliveries'))
            ->groupBy('delivery_drivers.id', 'delivery_drivers.name')
            ->orderByDesc('deliveries')
            ->limit(5)
            ->get();

        // Daily revenue for chart (last 7 or 30 days)
        $dailyRevenue = DB::table('orders')
            ->where('company_id', $company->id)
            ->where('status', OrderStatus::Delivered->value)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as revenue'), DB::raw('COUNT(*) as orders'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'period'          => $period,
            'date_from'       => $dateFrom->toDateString(),
            'date_to'         => $dateTo->toDateString(),
            'total_orders'    => $totalOrders,
            'delivered_orders'=> $deliveredOrders,
            'canceled_orders' => $canceledOrders,
            'active_orders'   => $activeOrders,
            'revenue'         => round($revenue, 2),
            'avg_ticket'      => round($avgTicket, 2),
            'top_products'    => $topProducts,
            'payment_methods' => $paymentMethods,
            'top_drivers'     => $topDrivers,
            'daily_revenue'   => $dailyRevenue,
        ];
    }

    private function resolvePeriod(string $period, array $filters): array
    {
        return match ($period) {
            'today'          => [now()->startOfDay(), now()->endOfDay()],
            'yesterday'      => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            'last_7_days'    => [now()->subDays(6)->startOfDay(), now()->endOfDay()],
            'last_30_days'   => [now()->subDays(29)->startOfDay(), now()->endOfDay()],
            'current_month'  => [now()->startOfMonth(), now()->endOfMonth()],
            'custom_range'   => [
                Carbon::parse($filters['date_from'])->startOfDay(),
                Carbon::parse($filters['date_to'])->endOfDay(),
            ],
            default          => [now()->startOfDay(), now()->endOfDay()],
        };
    }
}
