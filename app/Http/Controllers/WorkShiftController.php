<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsWithDataTables;
use App\Models\Employee;
use App\Models\Store;
use App\Models\WorkShift;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WorkShiftController extends Controller
{
    use RespondsWithDataTables;

    public function __construct()
    {
        $this->middleware(['auth', 'permission:hr.shifts.manage']);
    }

    public function index()
    {
        $employees = Employee::query()->orderBy('first_name')->orderBy('last_name')->get(['id', 'first_name', 'last_name']);
        $stores = Store::query()->where('status', 1)->orderBy('name')->get(['id', 'name']);

        return view('work-shifts.index', compact('employees', 'stores'));
    }

    public function listJson(Request $request): JsonResponse
    {
        $base = WorkShift::query()->with(['employee:id,first_name,last_name', 'store:id,name']);

        return $this->dataTablesOf(
            $base,
            $request,
            function (Builder $q, string $term) {
                $s = '%'.addcslashes($term, '%_\\').'%';
                $q->where(function ($w) use ($s) {
                    $w->where('status', 'like', $s)
                        ->orWhere('notes', 'like', $s)
                        ->orWhereHas('employee', fn ($e) => $e->where('first_name', 'like', $s)->orWhere('last_name', 'like', $s));
                });
            },
            fn (Builder $q) => $q->orderByDesc('shift_date')->orderByDesc('id'),
            function (WorkShift $row) {
                $name = e(trim($row->employee->first_name.' '.$row->employee->last_name));

                return [
                    'id' => $row->id,
                    'shift_date' => $row->shift_date?->format('Y-m-d') ?? '—',
                    'time' => e(substr((string) $row->starts_at, 0, 5).'–'.substr((string) $row->ends_at, 0, 5)),
                    'employee' => $name,
                    'store' => e($row->store->name ?? '—'),
                    'status' => e($row->status),
                    'actions' => '<button type="button" class="btn btn-sm btn-outline-primary btn-edit" data-id="'.$row->id.'">Edit</button> '
                        .'<button type="button" class="btn btn-sm btn-outline-danger btn-del" data-id="'.$row->id.'">Delete</button>',
                ];
            }
        );
    }

    public function showJson(WorkShift $workShift): JsonResponse
    {
        return response()->json(['shift' => $workShift]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'store_id' => ['nullable', 'exists:stores,id'],
            'shift_date' => ['required', 'date'],
            'starts_at' => ['required', 'date_format:H:i'],
            'ends_at' => ['required', 'date_format:H:i'],
            'break_minutes' => ['nullable', 'integer', 'min:0', 'max:480'],
            'status' => ['required', Rule::in(['scheduled', 'completed', 'cancelled', 'no_show'])],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $shift = WorkShift::create([
            'employee_id' => $data['employee_id'],
            'store_id' => $data['store_id'] ?? null,
            'shift_date' => $data['shift_date'],
            'starts_at' => $data['starts_at'].':00',
            'ends_at' => $data['ends_at'].':00',
            'break_minutes' => (int) ($data['break_minutes'] ?? 0),
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
        ]);

        return response()->json(['message' => 'Shift saved.', 'shift' => $shift]);
    }

    public function update(Request $request, WorkShift $workShift): JsonResponse
    {
        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'store_id' => ['nullable', 'exists:stores,id'],
            'shift_date' => ['required', 'date'],
            'starts_at' => ['required', 'date_format:H:i'],
            'ends_at' => ['required', 'date_format:H:i'],
            'break_minutes' => ['nullable', 'integer', 'min:0', 'max:480'],
            'status' => ['required', Rule::in(['scheduled', 'completed', 'cancelled', 'no_show'])],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $workShift->update([
            'employee_id' => $data['employee_id'],
            'store_id' => $data['store_id'] ?? null,
            'shift_date' => $data['shift_date'],
            'starts_at' => $data['starts_at'].':00',
            'ends_at' => $data['ends_at'].':00',
            'break_minutes' => (int) ($data['break_minutes'] ?? 0),
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
        ]);

        return response()->json(['message' => 'Shift updated.', 'shift' => $workShift->fresh()]);
    }

    public function destroy(WorkShift $workShift): JsonResponse
    {
        $workShift->delete();

        return response()->json(['message' => 'Shift removed.']);
    }

    public function destroyBulk(Request $request): JsonResponse
    {
        $data = $request->validate(['ids' => 'required|array|min:1', 'ids.*' => 'integer']);
        $deleted = WorkShift::whereIn('id', $data['ids'])->delete();
        return response()->json(['message' => __('common.bulk_deleted', ['count' => $deleted])]);
    }
}
