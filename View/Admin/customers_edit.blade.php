@extends('admin.partials.master')

@section('title') {{ __('Edit Customer') }} @endsection
@section('accounting_active') sidebar_active @endsection
@section('customers') active @endsection

@section('main-content')
<div class="aiz-titlebar d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">{{ __('Edit Customer') }}</h5>
    <a href="{{ route('admin.accounting.customers.index') }}" class="btn btn-secondary">
        <i class="las la-arrow-left"></i> {{ __('Back to List') }}
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.accounting.customers.update', $customer->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="form-group col-md-6">
                    <label>{{ __('Name') }} <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ $customer->name }}" required>
                </div>
                <div class="form-group col-md-6">
                    <label>{{ __('Email') }}</label>
                    <input type="email" name="email" class="form-control" value="{{ $customer->email }}">
                </div>
                <div class="form-group col-md-6">
                    <label>{{ __('Phone') }}</label>
                    <input type="text" name="phone" class="form-control" value="{{ $customer->phone }}">
                </div>
                <div class="form-group col-md-6">
                    <label>{{ __('Address') }}</label>
                    <input type="text" name="address" class="form-control" value="{{ $customer->address }}">
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-2">{{ __('Update') }}</button>
        </form>
    </div>
</div>
@endsection