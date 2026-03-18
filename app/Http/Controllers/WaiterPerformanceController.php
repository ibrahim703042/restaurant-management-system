<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsWithDataTables;
use App\Models\Employee;
use App\Models\WaiterPerformance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WaiterPerformanceController extends Controller
{
    use RespondsWithDataTables;

    public function __construct()
    {
        $this->middleware(['auth', 'permission:hr.performance.manage']);
    }

    public function index()
    {
        $employees = Employee::query()->orderBy('first_name')->orderBy('last_name')->get(['id', 'first_name', 'last_name']);

        return view('waiter-performance.index', compact('employees'));
    }

    public function listJson(Request $request): JsonResponse
    {
        $base = WaiterPerformance::query()->with(['employee:id,first_name,last_name', 'reviewer:id,name']);

        return $this->dataTablesOf(
            $base,
            $request,
            function (Builder $q, string $term) {
                $s = '%'.addcslashes($term, '%_\\').'%';
                $q->where(function ($w) use ($s) {
                    $w->where('comment', 'like', $s)
                        ->orWhereHas('employee', fn ($e) => $e->where('first_name', 'like', $s)->orWhere('last_name', 'like', $s));
                });
            },
            fn (Builder $q) => $q->orderByDesc('period_end')->orderByDesc('id'),
            function (WaiterPerformance $row) {
                $name = e(trim($row->employee->first_name.' '.$row->employee->last_name));

                return [
                    'id' => $row->id,
                    'employee' => $name,
                    'period' => e($row->period_start?->format('Y-m-d').' → '.$row->period_end?->format('Y-m-d')),
                    'rating' => (string) (int) $row->rating,
                    'tables' => $row->tables_served !== null ? (string) $row->tables_served : '—',
                    'sales' => $row->sales_total !== null ? number_format((float) $row->sales_total, 0) : '—',
                    'actions' => '<button type="button" class="btn btn-sm btn-outline-primary btn-edit" data-id="'.$row->id.'">Edit</button> '
                        .'<button type="button" class="btn btn-sm btn-outline-danger btn-del" data-id="'.$row->id.'">Delete</button>',
                ];
            }
        );
    }

    public function showJson(WaiterPerformance $waiterPerformance): JsonResponse
    {
        return response()->json(['performance' => $waiterPerformance]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->merge([
            'tables_served' => $request->input('tables_served') === '' || $request->input('tables_served') === null ? null : $request->input('tables_served'),
            'sales_total' => $request->input('sales_total') === '' || $request->input('sales_total') === null ? null : $request->input('sales_total'),
        ]);
        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'rating' => ['required', 'integer', Rule::in([1, 2, 3, 4, 5])],
            'tables_served' => ['nullable', 'integer', 'min:0'],
            'sales_total' => ['nullable', 'numeric', 'min:0'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $row = WaiterPerformance::create([
            'employee_id' => $data['employee_id'],
            'period_start' => $data['period_start'],
            'period_end' => $data['period_end'],
            'rating' => $data['rating'],
            'tables_served' => $data['tables_served'] ?? null,
            'sales_total' => $data['sales_total'] ?? null,
            'comment' => $data['comment'] ?? null,
            'reviewed_by' => auth()->id(),
        ]);

        return response()->json(['message' => 'Performance record saved.', 'performance' => $row]);
    }

    public function update(Request $request, WaiterPerformance $waiterPerformance): JsonResponse
    {
        $request->merge([
            'tables_served' => $request->input('tables_served') === '' || $request->input('tables_served') === null ? null : $request->input('tables_served'),
            'sales_total' => $request->input('sales_total') === '' || $request->input('sales_total') === null ? null : $request->input('sales_total'),
        ]);
        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'rating' => ['required', 'integer', Rule::in([1, 2, 3, 4, 5])],
            'tables_served' => ['nullable', 'integer', 'min:0'],
            'sales_total' => ['nullable', 'numeric', 'min:0'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $waiterPerformance->update([
            'employee_id' => $data['employee_id'],
            'period_start' => $data['period_start'],
            'period_end' => $data['period_end'],
            'rating' => $data['rating'],
            'tables_served' => $data['tables_served'] ?? null,
            'sales_total' => $data['sales_total'] ?? null,
            'comment' => $data['comment'] ?? null,
            'reviewed_by' => auth()->id(),
        ]);

        return response()->json(['message' => 'Performance updated.', 'performance' => $waiterPerformance->fresh()]);
    }

    public function destroy(WaiterPerformance $waiterPerformance): JsonResponse
    {
        $waiterPerformance->delete();

        return response()->json(['message' => 'Record removed.']);
    }
}
