@extends('layouts.admin')
@section('title', __('forms.module_tables'))
@section('page-title', __('forms.module_tables'))
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('nav.home') }}</a></li>
<li class="breadcrumb-item active">{{ __('forms.breadcrumb') }}</li>
@endsection
@section('main-section')
<div class="container-fluid px-lg-4">
    <x-forms.legacy-module-redirect permission="ops.dining_tables.manage" :href="route('tables.index')" :moduleName="__('forms.module_tables')" />
</div>
@endsection
