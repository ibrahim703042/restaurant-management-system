@extends('layouts.admin')
@section('main-section')
<div class="container-fluid px-4" style="max-width:640px">
    <h1 class="mt-4">New client</h1>
    <form method="post" action="{{ route('clients.store') }}" class="card card-body mt-3">
        @csrf
        <div class="mb-3"><label class="form-label">Name</label><input name="name" class="form-control" required value="{{ old('name') }}"></div>
        <div class="mb-3"><label class="form-label">Phone</label><input name="phone" class="form-control" value="{{ old('phone') }}"></div>
        <div class="mb-3"><label class="form-label">Address</label><textarea name="address" class="form-control">{{ old('address') }}</textarea></div>
        <button class="btn btn-primary">Save</button>
        <a href="{{ route('clients.index') }}" class="btn btn-link">Cancel</a>
    </form>
</div>
@endsection
