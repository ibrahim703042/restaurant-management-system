<?php

namespace App\Http\Controllers;

use App\Models\PayrollPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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

        return $this->dataTablesOf(
            PayrollPeriod::query(),
            $request,
            fn (Builder $q, string $term) => $q->where('notes', 'like', '%'.addcslashes($term, '%_\\').'%'),
            fn (Builder $q) => $q->orderByDesc('period_start'),
            function (PayrollPeriod $row) {
                return [
                    'id' => $row->id,
                    'period_start' => e((string) $row->period_start),
                    'period_end' => e((string) $row->period_end),
                    'status' => e((string) $row->status),
                    'notes' => e(Str::limit((string) $row->notes, 80)),
                ];
            }
        );
    }

    public function store(Request $request): JsonResponse|RedirectResponse
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
