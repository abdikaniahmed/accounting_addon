@extends('admin.partials.master')

@section('title') {{ __('Quick Expenses') }} @endsection
@section('accounting_active') sidebar_active @endsection
@section('quick_expenses') active @endsection

@section('main-content')
<div class="aiz-titlebar d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">{{ __('Quick Expenses') }}</h5>
    <a href="{{ route('admin.accounting.quick_expenses.create') }}" class="btn btn-primary">
        <i class="las la-plus"></i> {{ __('Add New Expense') }}
    </a>
</div>

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
                    @foreach($expenses as $expense)
                    <tr id="row_{{ $expense->id }}">
                        <td>{{ \Carbon\Carbon::parse($expense->date)->format('d M Y') }}</td>
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
                            <a href="{{ route('admin.accounting.quick_expenses.edit', $expense->id) }}"
                                class="btn btn-sm btn-outline-info" title="{{ __('Edit') }}">
                                <i class="las la-edit"></i>
                            </a>

                            <button onclick="delete_row('accounting/quick-expenses/', {{ $expense->id }})"
                                class="btn btn-sm btn-outline-danger" title="{{ __('Delete') }}">
                                <i class="las la-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach

                    @if($expenses->isEmpty())
                    <tr>
                        <td colspan="8" class="text-center text-muted">{{ __('No expense records found.') }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('script')
@include('admin.common.delete-ajax')
@endpush