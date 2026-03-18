<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsWithDataTables;
use App\Models\Employee;
use App\Models\EmployeeActivity;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeActivityController extends Controller
{
    use RespondsWithDataTables;

    public function __construct()
    {
        $this->middleware(['auth', 'permission:hr.activity.view']);
    }

    public function index()
    {
        $employees = Employee::query()->orderBy('first_name')->orderBy('last_name')->get(['id', 'first_name', 'last_name']);

        return view('employee-activities.index', compact('employees'));
    }

    public function listJson(Request $request): JsonResponse
    {
        $base = EmployeeActivity::query()->with(['employee:id,first_name,last_name', 'user:id,name']);

        if ($request->filled('employee_id')) {
            $base->where('employee_id', (int) $request->employee_id);
        }

        return $this->dataTablesOf(
            $base,
            $request,
            function (Builder $q, string $term) {
                $s = '%'.addcslashes($term, '%_\\').'%';
                $q->where(function ($w) use ($s) {
                    $w->where('action', 'like', $s)
                        ->orWhere('description', 'like', $s)
                        ->orWhere('entity_type', 'like', $s)
                        ->orWhereHas('employee', fn ($e) => $e->where('first_name', 'like', $s)->orWhere('last_name', 'like', $s))
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $s));
                });
            },
            fn (Builder $q) => $q->orderByDesc('created_at')->orderByDesc('id'),
            function (EmployeeActivity $row) {
                $name = e(trim($row->employee->first_name.' '.$row->employee->last_name));

                return [
                    'id' => $row->id,
                    'at' => $row->created_at->format('Y-m-d H:i'),
                    'employee' => $name,
                    'user' => e($row->user->name ?? '—'),
                    'action' => e($row->action),
                    'description' => e(Str::limit($row->description ?? '', 80)),
                ];
            }
        );
    }
}
