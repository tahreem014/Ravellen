<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SiteEvent;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $chartData = [
            'daily' => $this->dailySeries(),
            'weekly' => $this->weeklySeries(),
            'monthly' => $this->monthlySeries(),
        ];

        return view('admin.dashboard', $this->totals() + compact('chartData'));
    }

    /**
     * GET /admin/overview/stats?period=daily|weekly|monthly
     * Polled by the overview page every few seconds: the four totals plus the
     * chart series of the period being shown, so visits, clicks and orders
     * appear without a refresh.
     */
    public function stats(Request $request): JsonResponse
    {
        $period = (string) $request->query('period');

        $series = match ($period) {
            'daily' => $this->dailySeries(),
            'weekly' => $this->weeklySeries(),
            'monthly' => $this->monthlySeries(),
            default => null,
        };

        return response()->json($this->totals() + ['period' => $period, 'series' => $series]);
    }

    private function totals(): array
    {
        return [
            'websiteVisits' => SiteEvent::visits()->count(),
            'totalClicks' => SiteEvent::clicks()->count(),
            'totalOrders' => Order::count(),
            'revenue' => (float) Order::where('payment_status', 'paid')->sum('total_amount'),
        ];
    }

    private function dailySeries(): array
    {
        $categories = [];
        $sales = [];
        $visits = [];

        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::today()->subDays($i);
            $categories[] = $day->format('D');
            $sales[] = (float) Order::whereDate('order_date', $day)->sum('total_amount');
            $visits[] = SiteEvent::visits()->within($day->copy()->startOfDay(), $day->copy()->endOfDay())->count();
        }

        return compact('categories', 'sales', 'visits');
    }

    private function weeklySeries(): array
    {
        $categories = [];
        $sales = [];
        $visits = [];

        for ($i = 7; $i >= 0; $i--) {
            $weekStart = Carbon::now()->startOfWeek()->subWeeks($i);
            $weekEnd = (clone $weekStart)->endOfWeek();
            $categories[] = 'Wk ' . $weekStart->format('M j');
            $sales[] = (float) Order::whereBetween('order_date', [$weekStart, $weekEnd])->sum('total_amount');
            $visits[] = SiteEvent::visits()->within($weekStart, $weekEnd)->count();
        }

        return compact('categories', 'sales', 'visits');
    }

    private function monthlySeries(): array
    {
        $categories = [];
        $sales = [];
        $visits = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->startOfMonth()->subMonths($i);
            $categories[] = $month->format('M');
            $sales[] = (float) Order::whereYear('order_date', $month->year)
                ->whereMonth('order_date', $month->month)
                ->sum('total_amount');
            $visits[] = SiteEvent::visits()->within($month, $month->copy()->endOfMonth())->count();
        }

        return compact('categories', 'sales', 'visits');
    }
}
