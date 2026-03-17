<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $roles = Role::orderBy('name')->pluck('name', 'name');
        $employeesWithoutLogin = Employee::query()
            ->whereNull('user_id')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name']);

        return view('users.manage', compact('roles', 'employeesWithoutLogin'));
    }

    public function listJson(Request $request): JsonResponse
    {
        $q = User::query()
            ->with(['employee:id,first_name,last_name,user_id'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $s = '%'.$request->search.'%';
                $query->where(function ($q2) use ($s) {
                    $q2->where('name', 'like', $s)->orWhere('email', 'like', $s);
                });
            });

        $perPage = min(50, max(5, (int) $request->get('per_page', 15)));
        $paginated = $q->orderBy('name')->paginate($perPage);
        $rows = $paginated->getCollection()->map(function (User $u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role_names' => $u->getRoleNames()->values()->all(),
                'employee' => $u->employee ? [
                    'id' => $u->employee->id,
                    'first_name' => $u->employee->first_name,
                    'last_name' => $u->employee->last_name,
                ] : null,
            ];
        })->values();

        return response()->json([
            'data' => $rows,
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
            'total' => $paginated->total(),
        ]);
    }

    public function showJson(User $user): JsonResponse
    {
        $user->load('employee:id,first_name,last_name,user_id');

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->getRoleNames()->first(),
                'employee_id' => $user->employee?->id,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        if ($request->input('employee_id') === '' || $request->input('employee_id') === null) {
            $request->merge(['employee_id' => null]);
        }
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email',
                'password' => 'required|string|min:6|confirmed',
                'role' => 'required|string|exists:roles,name',
                'employee_id' => 'nullable|exists:employees,id',
            ]);
        } catch (ValidationException $e) {
            return $this->jsonErr($request, $e->errors(), 422);
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        $user->syncRoles([$data['role']]);

        if (! empty($data['employee_id'])) {
            $emp = Employee::query()->whereKey($data['employee_id'])->whereNull('user_id')->firstOrFail();
            $emp->update([
                'user_id' => $user->id,
                'can_access_app' => true,
            ]);
        }

        return $this->jsonOk($request, ['message' => 'User created.', 'user' => $user->load('employee')], redirect()->route('users.index')->with('status', 'User created.'));
    }

    public function update(Request $request, User $user): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        if ($user->id === auth()->id()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'Cannot edit your own account here.', 'errors' => ['user' => ['Use profile settings.']]], 422);
            }

            return back()->withErrors(['user' => 'Cannot edit self here.']);
        }

        if ($request->input('employee_id') === '' || $request->input('employee_id') === null) {
            $request->merge(['employee_id' => null]);
        }

        try {
            $rules = [
                'name' => 'required|string|max:255',
                'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
                'role' => 'required|string|exists:roles,name',
                'employee_id' => 'nullable|exists:employees,id',
            ];
            if ($request->filled('password')) {
                $rules['password'] = 'required|string|min:6|confirmed';
            }
            $data = $request->validate($rules);
        } catch (ValidationException $e) {
            return $this->jsonErr($request, $e->errors(), 422);
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        if (! empty($data['password'] ?? null)) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();
        $user->syncRoles([$data['role']]);

        Employee::query()->where('user_id', $user->id)->update(['user_id' => null, 'can_access_app' => false]);
        if (! empty($data['employee_id'])) {
            $emp = Employee::query()->whereKey($data['employee_id'])->where(function ($q) use ($user) {
                $q->whereNull('user_id')->orWhere('user_id', $user->id);
            })->firstOrFail();
            $emp->update(['user_id' => $user->id, 'can_access_app' => true]);
        }

        return $this->jsonOk($request, ['message' => 'User updated.', 'user' => $user->fresh()->load('employee')], redirect()->route('users.index')->with('status', 'User updated.'));
    }

    public function destroy(Request $request, User $user): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        if ($user->id === auth()->id()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'You cannot delete your own account.'], 422);
            }

            return back()->withErrors(['delete' => 'Cannot delete self.']);
        }
        Employee::query()->where('user_id', $user->id)->update(['user_id' => null, 'can_access_app' => false]);
        $user->delete();

        return $this->jsonOk($request, ['message' => 'User removed.'], redirect()->route('users.index')->with('status', 'User removed.'));
    }

    public function employeesAvailableJson(): JsonResponse
    {
        $q = Employee::query()->whereNull('user_id')->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name']);

        return response()->json(['employees' => $q]);
    }

    public function employeesForEditJson(User $user): JsonResponse
    {
        $q = Employee::query()->orderBy('first_name')
            ->where(function ($q2) use ($user) {
                $q2->whereNull('user_id')->orWhere('user_id', $user->id);
            })
            ->get(['id', 'first_name', 'last_name', 'user_id']);

        return response()->json(['employees' => $q]);
    }

    private function jsonOk(Request $request, array $json, $redirect)
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($json);
        }

        return $redirect;
    }

    private function jsonErr(Request $request, array $errors, int $code): JsonResponse
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Validation failed.', 'errors' => $errors], $code);
        }
        throw ValidationException::withMessages($errors);
    }
}
