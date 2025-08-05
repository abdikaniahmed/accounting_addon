@extends('admin.partials.master')

@section('title') {{ __('Add Vendor') }} @endsection
@section('accounting_active') sidebar_active @endsection
@section('vendors') active @endsection

@section('main-content')
<div class="aiz-titlebar d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">{{ __('Add New Vendor') }}</h5>
    <a href="{{ route('admin.accounting.vendors.index') }}" class="btn btn-secondary">
        <i class="las la-arrow-left"></i> {{ __('Back to List') }}
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.accounting.vendors.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="form-group col-md-6">
                    <label>{{ __('Name') }} <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group col-md-6">
                    <label>{{ __('Email') }}</label>
                    <input type="email" name="email" class="form-control">
                </div>
                <div class="form-group col-md-6">
                    <label>{{ __('Phone') }}</label>
                    <input type="text" name="phone" class="form-control">
                </div>
                <div class="form-group col-md-6">
                    <label>{{ __('Address') }}</label>
                    <input type="text" name="address" class="form-control">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">{{ __('Save Vendor') }}</button>
        </form>
    </div>
</div>
@endsection