@extends('layouts.admin')
@section('main-section')
<div class="container-fluid px-4" style="max-width:640px">
    <h1 class="mt-4">Edit client</h1>
    <form method="post" action="{{ route('clients.update', $client) }}" class="card card-body mt-3">
        @csrf @method('PUT')
        <div class="mb-3"><label class="form-label">Name</label><input name="name" class="form-control" required value="{{ old('name', $client->name) }}"></div>
        <div class="mb-3"><label class="form-label">Phone</label><input name="phone" class="form-control" value="{{ old('phone', $client->phone) }}"></div>
        <div class="mb-3"><label class="form-label">Address</label><textarea name="address" class="form-control">{{ old('address', $client->address) }}</textarea></div>
        <button class="btn btn-primary">Update</button>
        <a href="{{ route('clients.index') }}" class="btn btn-link">Back</a>
    </form>
</div>
@endsection
