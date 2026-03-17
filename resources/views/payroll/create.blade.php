@extends('layouts.admin')
@section('main-section')
<div class="container-fluid px-4" style="max-width:480px">
    <h1 class="mt-4">New payroll period</h1>
    <form method="post" action="{{ route('payroll.store') }}" class="card card-body mt-3">
        @csrf
        <div class="mb-3"><label class="form-label">Period start</label><input type="date" name="period_start" class="form-control" required value="{{ old('period_start') }}"></div>
        <div class="mb-3"><label class="form-label">Period end</label><input type="date" name="period_end" class="form-control" required value="{{ old('period_end') }}"></div>
        <div class="mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control">{{ old('notes') }}</textarea></div>
        <button class="btn btn-primary">Save draft</button>
        <a href="{{ route('payroll.index') }}" class="btn btn-link">Back</a>
    </form>
</div>
@endsection
