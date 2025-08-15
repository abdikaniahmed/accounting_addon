@extends('admin.partials.master')

@section('title') {{ __('Vendor Bills') }} @endsection
@section('accounting_active') sidebar_active @endsection
@section('bills') active @endsection

@section('main-content')
<div class="aiz-titlebar d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">{{ __('Vendor Bills (A/P)') }}</h5>
    <div>
        <a href="{{ route('admin.accounting.bills.create') }}" class="btn btn-primary">
            <i class="bx bx-plus"></i> {{ __('Record Bill') }}
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body table-responsive">
        <table class="table table-bordered aiz-table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('Bill No') }}</th>
                    <th>{{ __('Vendor') }}</th>
                    <th>{{ __('Bill Date') }}</th>
                    <th class="text-right">{{ __('Total') }}</th>
                    <th class="text-right">{{ __('Paid') }}</th>
                    <th class="text-right">{{ __('Due') }}</th>
                    <th class="text-center">{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bills as $bill)
                @php
                // if you added casts, bill_date is a Carbon; otherwise parse
                $billDate = $bill->bill_date instanceof \Carbon\Carbon
                ? $bill->bill_date->format('Y-m-d')
                : (\Illuminate\Support\Carbon::parse($bill->bill_date)->format('Y-m-d'));

                $paid = (float) ($bill->payments->sum('amount') ?? 0);
                $total = (float) ($bill->total_amount ?? 0);
                $due = (float) ($bill->balance_due ?? max(0, $total - $paid));
                @endphp
                <tr id="row_{{ $bill->id }}">
                    <td>{{ $loop->iteration }}</td>
                    <td><span class="badge badge-info">{{ $bill->bill_number }}</span></td>
                    <td>{{ $bill->vendor?->name ?? '-' }}</td>
                    <td>{{ $billDate }}</td>
                    <td class="text-right">{{ number_format($total, 2) }}</td>
                    <td class="text-right">{{ number_format($paid, 2) }}</td>
                    <td class="text-right">{{ number_format($due, 2) }}</td>
                    <td class="text-center">
                        <a href="{{ route('admin.accounting.bills.edit', $bill->id) }}"
                            class="btn btn-sm btn-outline-primary" title="{{ __('Edit') }}">
                            <i class="bx bx-edit"></i>
                        </a>

                        <a href="{{ route('admin.accounting.bills.pay.create', $bill->id) }}"
                            class="btn btn-sm btn-outline-success" title="{{ __('Pay Bill') }}">
                            <i class="bx bx-credit-card"></i>
                        </a>

                        <a href="javascript:void(0);" onclick="delete_row('accounting/bills/', {{ $bill->id }})"
                            class="btn btn-sm btn-outline-danger" title="{{ __('Delete') }}">
                            <i class="bx bx-trash"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted">{{ __('No bills found.') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-3">
            {{ $bills->links() }}
        </div>
    </div>
</div>
@endsection

@push('script')
@include('admin.common.delete-ajax')
@endpush