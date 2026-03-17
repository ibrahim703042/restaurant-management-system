@extends('layouts.admin')
@section('main-section')
<div class="container-fluid px-4" style="max-width:560px">
    <h1 class="mt-4">Add user account</h1>
    <p class="text-muted small">Employees who should log in: pick their record below (optional). Others stay staff-only.</p>
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif
    <form method="post" action="{{ route('users.store') }}" class="card card-body mt-3">
        @csrf
        <div class="mb-3">
            <label class="form-label">Full name</label>
            <input name="name" class="form-control" required value="{{ old('name') }}">
        </div>
        <div class="mb-3">
            <label class="form-label">Email (login)</label>
            <input type="email" name="email" class="form-control" required value="{{ old('email') }}">
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required minlength="6">
        </div>
        <div class="mb-3">
            <label class="form-label">Confirm password</label>
            <input type="password" name="password_confirmation" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Role</label>
            <select name="role" class="form-select" required>
                @foreach ($roles as $r)
                <option value="{{ $r }}" {{ old('role') === $r ? 'selected' : '' }}>{{ $r }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Link employee (optional)</label>
            <select name="employee_id" class="form-select">
                <option value="">— No employee link —</option>
                @foreach ($employeesWithoutLogin as $e)
                <option value="{{ $e->id }}" {{ old('employee_id') == $e->id ? 'selected' : '' }}>
                    {{ $e->first_name }} {{ $e->last_name }} ({{ $e->email }})
                </option>
                @endforeach
            </select>
        </div>
        <button class="btn btn-primary">Create user</button>
        <a href="{{ route('users.index') }}" class="btn btn-link">Cancel</a>
    </form>
</div>
@endsection
