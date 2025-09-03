@extends('admin.partials.master') {{-- or your seller master --}}

@section('title', __('Quick Expenses'))

@section('main-content')
<div class="aiz-titlebar d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">{{ __('Quick Expenses') }}</h5>
    <a href="{{ route('seller.accounting.quick_expenses.create') }}" class="btn btn-primary">
        <i class="las la-plus"></i> {{ __('Add New Expense') }}
    </a>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('seller.accounting.quick_expenses.index') }}" class="card mb-3">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-md-4">
                <input type="text" name="q" class="form-control" value="{{ request('q') }}"
                    placeholder="{{ __('Search (vendor, ref, description)') }}">
            </div>
            <div class="col-md-3">
                <input type="date" name="start" class="form-control" value="{{ request('start') }}">
            </div>
            <div class="col-md-3">
                <input type="date" name="end" class="form-control" value="{{ request('end') }}">
            </div>
            <div class="col-md-2 text-right">
                <button class="btn btn-outline-secondary w-100" type="submit"><i class="las la-search"></i>
                    {{ __('Filter') }}</button>
            </div>
        </div>
    </div>
</form>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table aiz-table mb-0">
                <thead>
                    <tr>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Expense Account') }}</th>
                        <th>{{ __('Payment Account') }}</th>
                        <th>{{ __('Vendor') }}</th>
                        <th>{{ __('Reference') }}</th>
                        <th class="text-right">{{ __('Amount') }}</th>
                        <th>{{ __('Bill') }}</th>
                        <th class="text-right">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $expense)
                    <tr id="row_{{ $expense->id }}">
                        <td>{{ optional($expense->date)->format('d M Y') }}</td>
                        <td>{{ $expense->expenseAccount->name ?? '-' }}</td>
                        <td>{{ $expense->paymentAccount->name ?? '-' }}</td>
                        <td>{{ $expense->vendor ?? '-' }}</td>
                        <td>{{ $expense->reference ?? '-' }}</td>
                        <td class="text-right">{{ format_price($expense->amount) }}</td>
                        <td>
                            @if($expense->bill_file)
                            <a href="{{ uploaded_asset($expense->bill_file) }}" target="_blank">{{ __('View') }}</a>
                            @else
                            -
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="{{ route('seller.accounting.quick_expenses.edit', $expense->id) }}"
                                class="btn btn-sm btn-outline-info" title="{{ __('Edit') }}">
                                <i class="bx bx-edit"></i>
                            </a>
                            <button onclick="delete_row('seller/accounting/quick-expenses/', {{ $expense->id }})"
                                class="btn btn-sm btn-outline-danger" title="{{ __('Delete') }}">
                                <i class="bx bx-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">{{ __('No expense records found.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('script')
@include('admin.common.delete-ajax')
@endpush