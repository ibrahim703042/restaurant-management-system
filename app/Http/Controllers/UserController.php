<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::query()->with('employee')->orderBy('name')->paginate(25);

        return view('pages.tables.userTable', compact('users'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->pluck('name', 'name');
        $employeesWithoutLogin = Employee::query()
            ->whereNull('user_id')
            ->orderBy('first_name')
            ->get();

        return view('pages.forms.user', compact('roles', 'employeesWithoutLogin'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|string|exists:roles,name',
            'employee_id' => 'nullable|exists:employees,id',
        ]);

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

        return redirect()->route('users.index')->with('status', 'User created. Employee linked if selected.');
    }
}
