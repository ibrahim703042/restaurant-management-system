<?php

namespace App\Http\Controllers;

use App\Models\PayrollPeriod;
use Illuminate\Http\Request;

class PayrollPeriodController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->authorizePermission('hr.payroll.view');
        $periods = PayrollPeriod::orderByDesc('period_start')->paginate(20);

        return view('payroll.index', compact('periods'));
    }

    public function create()
    {
        $this->authorizePermission('hr.payroll.manage');

        return view('payroll.create');
    }

    public function store(Request $request)
    {
        $this->authorizePermission('hr.payroll.manage');
        $data = $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'notes' => 'nullable|string|max:2000',
        ]);
        PayrollPeriod::create($data + ['status' => 'draft']);

        return redirect()->route('payroll.index')->with('status', 'Payroll period created (draft).');
    }

    private function authorizePermission(string $permission): void
    {
        if (! auth()->user()->can($permission)) {
            abort(403);
        }
    }
}
