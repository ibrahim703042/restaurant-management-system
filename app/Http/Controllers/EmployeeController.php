<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Position;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EmployeeController extends Controller
{
    public function index()
    {
        $positions = Position::orderBy('title')->get();

        return view('employees.manage', compact('positions'));
    }

    public function listJson(Request $request): JsonResponse
    {
        $base = Employee::query()
            ->with('position:id,title')
            ->when($request->filled('position_id'), fn ($query) => $query->where('position_id', $request->position_id));

        return $this->dataTablesOf(
            $base,
            $request,
            function (Builder $q, string $term) {
                $s = '%'.addcslashes($term, '%_\\').'%';
                $q->where(function ($q2) use ($s) {
                    $q2->where('first_name', 'like', $s)
                        ->orWhere('last_name', 'like', $s)
                        ->orWhere('email', 'like', $s)
                        ->orWhere('phone', 'like', $s);
                });
            },
            fn (Builder $q) => $q->orderByDesc('id'),
            function (Employee $row) {
                $name = e(trim($row->first_name.' '.$row->last_name));

                return [
                    'id' => $row->id,
                    'name' => $name,
                    'email' => e((string) $row->email),
                    'phone' => e((string) $row->phone),
                    'position' => e($row->position->title ?? '—'),
                    'actions' => '<button type="button" class="btn btn-sm btn-outline-primary btn-edit" data-id="'.$row->id.'">Edit</button> '
                        .'<button type="button" class="btn btn-sm btn-outline-danger btn-del" data-id="'.$row->id.'">Delete</button>',
                ];
            }
        );
    }

    public function showJson(Employee $employee): JsonResponse
    {
        $employee->load('position:id,title');

        return response()->json(['employee' => $employee]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        try {
            $data = $this->validatedEmployee($request, true, null);
        } catch (ValidationException $e) {
            return $this->jsonOrRedirect($request, $e->errors(), 422);
        }

        $imagePath = '';
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('employees', 'public');
        }

        $employee = Employee::create(array_merge($data, [
            'image' => $imagePath ?: 'default.png',
            'position_id' => $data['position_id'] ?: Position::query()->value('id'),
        ]));

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Employee created.', 'employee' => $employee->load('position')]);
        }

        return redirect()->route('employees.index')->with('status', 'Employee created.');
    }

    public function update(Request $request, Employee $employee): JsonResponse|RedirectResponse
    {
        try {
            $data = $this->validatedEmployee($request, false, $employee);
        } catch (ValidationException $e) {
            return $this->jsonOrRedirect($request, $e->errors(), 422);
        }

        $employee->fill([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'gender' => $data['gender'],
            'birthday' => $data['birthday'],
            'phone' => $data['phone'],
            'mother_name' => $data['mother_name'],
            'father_name' => $data['father_name'],
            'country' => $data['country'],
            'city' => $data['city'],
            'address' => $data['address'],
            'position_id' => $data['position_id'] ?: $employee->position_id,
        ]);
        if ($request->hasFile('image')) {
            $employee->image = $request->file('image')->store('employees', 'public');
        }
        $employee->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Employee updated.', 'employee' => $employee->fresh()->load('position')]);
        }

        return redirect()->route('employees.index')->with('status', 'Employee updated.');
    }

    public function destroy(Request $request, Employee $employee): JsonResponse|RedirectResponse
    {
        $employee->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Employee removed.']);
        }

        return redirect()->route('employees.index')->with('status', 'Employee removed.');
    }

    private function validatedEmployee(Request $request, bool $isCreate, ?Employee $employee): array
    {
        $emailRules = ['required', 'email', 'max:255', Rule::unique('employees', 'email')];
        if (! $isCreate && $employee) {
            $emailRules = ['required', 'email', 'max:255', Rule::unique('employees', 'email')->ignore($employee->id)];
        }

        $rules = [
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'email' => $emailRules,
            'gender' => 'required|in:Male,Female,Other',
            'birthdate' => 'required|date',
            'phone' => 'required|string|max:50',
            'mother' => 'required|string|max:255',
            'father' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'position_id' => 'nullable|exists:positions,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ];

        $request->validate($rules);

        return [
            'first_name' => $request->fname,
            'last_name' => $request->lname,
            'email' => $request->email,
            'gender' => $request->gender,
            'birthday' => $request->birthdate,
            'phone' => $request->phone,
            'mother_name' => $request->mother,
            'father_name' => $request->father,
            'country' => $request->country,
            'city' => $request->city,
            'address' => $request->address,
            'position_id' => $request->input('position_id'),
        ];
    }

    private function jsonOrRedirect(Request $request, array $errors, int $code): JsonResponse
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Validation failed.', 'errors' => $errors], $code);
        }

        throw ValidationException::withMessages($errors);
    }
}
