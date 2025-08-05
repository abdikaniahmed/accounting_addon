@extends('admin.partials.master')

@section('title') {{ __('Vendors') }} @endsection
@section('accounting_active') sidebar_active @endsection
@section('vendors') active @endsection

@section('main-content')
<div class="aiz-titlebar d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">{{ __('Vendor List') }}</h5>
    <a href="{{ route('admin.accounting.vendors.create') }}" class="btn btn-primary">
        <i class="las la-plus"></i> {{ __('Add New Vendor') }}
    </a>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-bordered aiz-table mb-0">
            <thead>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Email') }}</th>
                    <th>{{ __('Phone') }}</th>
                    <th>{{ __('Address') }}</th>
                    <th class="text-center">{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vendors as $vendor)
                <tr id="row_{{ $vendor->id }}">
                    <td>{{ $vendor->name }}</td>
                    <td>{{ $vendor->email }}</td>
                    <td>{{ $vendor->phone }}</td>
                    <td>{{ $vendor->address }}</td>
                    <td class="text-center">
                        <a href="{{ route('admin.accounting.vendors.edit', $vendor->id) }}"
                            class="btn btn-sm btn-circle btn-outline-info" title="{{ __('Edit') }}">
                            <i class="bx bx-edit"></i>
                        </a>
                        <a href="javascript:void(0);" onclick="delete_row('accounting/vendors/', {{ $vendor->id }})"
                            class="btn btn-sm btn-circle btn-outline-danger" data-toggle="tooltip"
                            title="{{ __('Delete') }}">
                            <i class="bx bx-trash"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">{{ __('No vendors found.') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('script')
@include('admin.common.delete-ajax')
@endpush