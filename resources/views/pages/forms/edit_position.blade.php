@extends('layouts.admin')
@section('title', __('forms.module_positions'))
@section('page-title', __('forms.module_positions'))
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('nav.home') }}</a></li>
<li class="breadcrumb-item active">{{ __('forms.breadcrumb') }}</li>
@endsection
@section('main-section')
<div class="container-fluid px-lg-4">
    <x-forms.legacy-module-redirect permission="hr.positions.manage" :href="route('positions.index')" :moduleName="__('forms.module_positions')" />
</div>
@endsection
