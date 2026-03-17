@extends('layouts.admin')
@section('main-section')
<div class="container-fluid px-4">
    <h1 class="mt-4">Feature configuration</h1>
    <p class="text-muted">Grouped toggles. When off, related sidebar items stay hidden.</p>
    @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    @foreach ($settings->groupBy('setting_group') as $group => $items)
    <h5 class="mt-4 text-capitalize">{{ str_replace('_', ' ', $group) }}</h5>
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            @foreach ($items as $s)
            <form method="post" action="{{ route('settings.update', $s) }}" class="row align-items-center py-2 border-bottom">
                @csrf @method('PUT')
                <div class="col-md-5"><code>{{ $s->key }}</code></div>
                <div class="col-md-4 d-flex flex-wrap gap-3 align-items-center">
                    <x-admin.radio name="is_active" value="1" label="On" :checked="$s->is_active" onchange="this.form.submit()" />
                    <x-admin.radio name="is_active" value="0" label="Off" :checked="! $s->is_active" onchange="this.form.submit()" />
                </div>
                <input type="hidden" name="value" value="{{ $s->value }}">
            </form>
            @endforeach
        </div>
    </div>
    @endforeach
</div>
@endsection
