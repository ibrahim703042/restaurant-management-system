@extends('layouts.admin')
@section('title', __('forms.module_products'))
@section('page-title', __('forms.module_products'))
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">{{ __('nav.home') }}</a></li>
<li class="breadcrumb-item active">{{ __('forms.breadcrumb') }}</li>
@endsection
@section('main-section')
<div class="container-fluid px-lg-4">
    <x-forms.legacy-module-redirect permission="ops.menu.products.manage" :href="route('products.index')" :moduleName="__('forms.module_products')" />
</div>
@endsection
