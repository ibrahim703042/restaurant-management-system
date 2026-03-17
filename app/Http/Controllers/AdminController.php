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

        $stats = [
            'orders_total' => Order::count(),
            'clients_total' => Client::count(),
            'unpaid_debts' => Client::sum('debt_balance'),
            'income_today' => $incomeToday,
            'income_week' => $incomeWeek,
            'income_month' => $incomeMonth,
        ];

        $recentPayments = Payment::query()
            ->with(['bill', 'client', 'user'])
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('admin.index', compact('stats', 'recentPayments'));
    }
}
