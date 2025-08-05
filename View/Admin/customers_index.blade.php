@extends('admin.partials.master')

@section('title') {{ __('Customers') }} @endsection
@section('accounting_active') sidebar_active @endsection
@section('customers') active @endsection

@section('main-content')
<div class="aiz-titlebar d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">{{ __('Customer List') }}</h5>
    <a href="{{ route('admin.accounting.customers.create') }}" class="btn btn-primary">
        <i class="las la-plus"></i> {{ __('Add New Customer') }}
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
                    <th class="text-center">{{ __('Action') }}</th> <!-- NEW -->
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                <tr id="row_{{ $customer->id }}">
                    <td>{{ $customer->name }}</td>
                    <td>{{ $customer->email }}</td>
                    <td>{{ $customer->phone }}</td>
                    <td>{{ $customer->address }}</td>
                    <td class="text-center">
                        <a href="{{ route('admin.accounting.customers.edit', $customer->id) }}"
                            class="btn btn-sm btn-circle btn-outline-info" title="Edit">
                            <i class="bx bx-edit"></i>
                        </a>

                        <a href="javascript:void(0);" onclick="delete_row('accounting/customers/', {{ $customer->id }})"
                            class="btn btn-sm btn-circle btn-outline-danger" data-toggle="tooltip"
                            title="{{ __('Delete') }}">
                            <i class="bx bx-trash"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">{{ __('No customers found.') }}</td>
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