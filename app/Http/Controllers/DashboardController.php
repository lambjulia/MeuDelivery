<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboardService)
    {
    }

    public function index(Request $request): Response
    {
        $metrics = $this->dashboardService->getMetrics(
            $request->user()->company,
            $request->only(['period', 'date_from', 'date_to'])
        );

        return Inertia::render('Dashboard', [
            'metrics' => $metrics,
            'period'  => $request->input('period', 'today'),
            'filters' => $request->only(['period', 'date_from', 'date_to']),
        ]);
    }
}
