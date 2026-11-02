<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SiteEvent;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $totalOrders = Order::count();
        $revenue = Order::where('payment_status', 'paid')->sum('total_amount');

        $websiteVisits = SiteEvent::visits()->count();
        $totalClicks = SiteEvent::clicks()->count();

        $chartData = [
            'daily' => $this->dailySeries(),
            'weekly' => $this->weeklySeries(),
            'monthly' => $this->monthlySeries(),
        ];

        return view('admin.dashboard', compact('totalOrders', 'revenue', 'websiteVisits', 'totalClicks', 'chartData'));
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
