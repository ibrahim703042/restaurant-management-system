<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Order;
use App\Models\Payment;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:sales.dashboard.view']);
    }

    public function index()
    {
        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $monthStart = Carbon::now()->startOfMonth();

        $incomeToday = Payment::query()->whereDate('created_at', $today)->sum('amount');
        $incomeWeek = Payment::query()->where('created_at', '>=', $weekStart)->sum('amount');
        $incomeMonth = Payment::query()->where('created_at', '>=', $monthStart)->sum('amount');

        $prevWeekStart = Carbon::now()->subWeek()->startOfWeek();
        $prevWeekEnd = Carbon::now()->subWeek()->endOfWeek();
        $incomePrevWeek = (float) Payment::query()->whereBetween('created_at', [$prevWeekStart, $prevWeekEnd])->sum('amount');
        $weekTrend = $incomePrevWeek > 0
            ? round((($incomeWeek - $incomePrevWeek) / $incomePrevWeek) * 100, 1)
            : null;

        $stats = [
            'orders_total' => Order::count(),
            'clients_total' => Client::count(),
            'unpaid_debts' => Client::sum('debt_balance'),
            'income_today' => $incomeToday,
            'income_week' => $incomeWeek,
            'income_month' => $incomeMonth,
            'income_prev_week' => $incomePrevWeek,
            'week_trend_pct' => $weekTrend,
            'payments_today_count' => Payment::query()->whereDate('created_at', $today)->count(),
        ];

        $recentPayments = Payment::query()
            ->with(['bill:id,bill_number,order_id', 'client:id,name', 'user' => fn ($q) => $q->with('employee:id,user_id,image')])
            ->orderByDesc('id')
            ->limit(12)
            ->get();

        return view('admin.index', compact('stats', 'recentPayments'));
    }
}
