<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsWithDataTables;
use App\Models\Employee;
use App\Models\EmployeeLeave;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeLeaveController extends Controller
{
    use RespondsWithDataTables;

    public function __construct()
    {
        $this->middleware(['auth', 'permission:hr.leaves.manage']);
    }

    public function index()
    {
        $employees = Employee::query()->orderBy('first_name')->orderBy('last_name')->get(['id', 'first_name', 'last_name']);

        return view('employee-leaves.index', compact('employees'));
    }

    public function listJson(Request $request): JsonResponse
    {
        $base = EmployeeLeave::query()->with(['employee:id,first_name,last_name', 'approver:id,name']);

        return $this->dataTablesOf(
            $base,
            $request,
            function (Builder $q, string $term) {
                $s = '%'.addcslashes($term, '%_\\').'%';
                $q->where(function ($w) use ($s) {
                    $w->where('leave_type', 'like', $s)
                        ->orWhere('status', 'like', $s)
                        ->orWhere('reason', 'like', $s)
                        ->orWhereHas('employee', fn ($e) => $e->where('first_name', 'like', $s)->orWhere('last_name', 'like', $s));
                });
            },
            fn (Builder $q) => $q->orderByDesc('start_date')->orderByDesc('id'),
            function (EmployeeLeave $row) {
                $name = e(trim($row->employee->first_name.' '.$row->employee->last_name));
                $pending = $row->status === 'pending';
                $btns = '<button type="button" class="btn btn-sm btn-outline-primary btn-edit" data-id="'.$row->id.'">Edit</button> ';
                if ($pending) {
                    $btns .= '<button type="button" class="btn btn-sm btn-success btn-approve" data-id="'.$row->id.'">Approve</button> '
                        .'<button type="button" class="btn btn-sm btn-warning btn-reject" data-id="'.$row->id.'">Reject</button> ';
                }
                $btns .= '<button type="button" class="btn btn-sm btn-outline-danger btn-del" data-id="'.$row->id.'">Delete</button>';

                return [
                    'id' => $row->id,
                    'employee' => $name,
                    'leave_type' => e($row->leave_type),
                    'range' => e($row->start_date?->format('Y-m-d').' → '.$row->end_date?->format('Y-m-d')),
                    'status' => e($row->status),
                    'approver' => e($row->approver->name ?? '—'),
                    'actions' => $btns,
                ];
            }
        );
    }

    public function showJson(EmployeeLeave $employee_leave): JsonResponse
    {
        return response()->json(['leave' => $employee_leave]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'leave_type' => ['required', 'string', 'max:64'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
            'reason' => ['nullable', 'string', 'max:2000'],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $leave = EmployeeLeave::create([
            'employee_id' => $data['employee_id'],
            'leave_type' => $data['leave_type'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'status' => $data['status'],
            'reason' => $data['reason'] ?? null,
            'admin_note' => $data['admin_note'] ?? null,
            'approved_by' => in_array($data['status'], ['approved', 'rejected'], true) ? auth()->id() : null,
            'decided_at' => in_array($data['status'], ['approved', 'rejected'], true) ? now() : null,
        ]);

        return response()->json(['message' => 'Leave record saved.', 'leave' => $leave]);
    }

    public function update(Request $request, EmployeeLeave $employee_leave): JsonResponse
    {
        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'leave_type' => ['required', 'string', 'max:64'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
            'reason' => ['nullable', 'string', 'max:2000'],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $employee_leave->update([
            'employee_id' => $data['employee_id'],
            'leave_type' => $data['leave_type'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'status' => $data['status'],
            'reason' => $data['reason'] ?? null,
            'admin_note' => $data['admin_note'] ?? null,
            'approved_by' => in_array($data['status'], ['approved', 'rejected'], true) ? auth()->id() : null,
            'decided_at' => in_array($data['status'], ['approved', 'rejected'], true) ? now() : null,
        ]);

        return response()->json(['message' => 'Leave updated.', 'leave' => $employee_leave->fresh()]);
    }

    public function decide(Request $request, EmployeeLeave $employee_leave): JsonResponse
    {
        $data = $request->validate([
            'decision' => ['required', Rule::in(['approved', 'rejected'])],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $employee_leave->update([
            'status' => $data['decision'],
            'approved_by' => auth()->id(),
            'decided_at' => now(),
            'admin_note' => $data['admin_note'] ?? $employee_leave->admin_note,
        ]);

        return response()->json(['message' => 'Leave '.$data['decision'].'.', 'leave' => $employee_leave->fresh()]);
    }

    public function destroy(EmployeeLeave $employee_leave): JsonResponse
    {
        $employee_leave->delete();

        return response()->json(['message' => 'Leave record removed.']);
    }

    public function destroyBulk(Request $request): JsonResponse
    {
        $data = $request->validate(['ids' => 'required|array|min:1', 'ids.*' => 'integer']);
        $deleted = EmployeeLeave::whereIn('id', $data['ids'])->delete();
        return response()->json(['message' => __('common.bulk_deleted', ['count' => $deleted])]);
    }
}
