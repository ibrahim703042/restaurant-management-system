<?php

namespace App\Http\Controllers;

use App\Models\PayrollPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PayrollPeriodController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->authorizePermission('hr.payroll.view');

        return view('payroll.manage');
    }

    public function listJson(Request $request): JsonResponse
    {
        $this->authorizePermission('hr.payroll.view');

        $q = PayrollPeriod::query()
            ->when($request->filled('search'), fn ($query) => $query->where('notes', 'like', '%'.$request->search.'%'));

        $perPage = min(50, max(5, (int) $request->get('per_page', 15)));
        $paginated = $q->orderByDesc('period_start')->paginate($perPage);

        return response()->json([
            'data' => $paginated->items(),
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
            'total' => $paginated->total(),
        ]);
    }

    public function store(Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $this->authorizePermission('hr.payroll.manage');

        try {
            $data = $request->validate([
                'period_start' => 'required|date',
                'period_end' => 'required|date|after_or_equal:period_start',
                'notes' => 'nullable|string|max:2000',
            ]);
        } catch (ValidationException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'Validation failed.', 'errors' => $e->errors()], 422);
            }
            throw $e;
        }

        $period = PayrollPeriod::create($data + ['status' => 'draft']);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Payroll period created (draft).', 'period' => $period]);
        }

        return redirect()->route('payroll.index')->with('status', 'Payroll period created (draft).');
    }

    private function authorizePermission(string $permission): void
    {
        if (! auth()->user()->can($permission)) {
            abort(403);
        }
    }
}
