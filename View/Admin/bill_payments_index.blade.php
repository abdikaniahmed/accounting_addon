@extends('admin.partials.master')

@section('title') {{ __('Bill Payments') }} @endsection
@section('accounting_active') sidebar_active @endsection
@section('bill_payments') active @endsection

@section('main-content')
<div class="aiz-titlebar d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">{{ __('Bill Payments') }}</h5>
    <a href="{{ route('admin.accounting.bills.index') }}" class="btn btn-secondary">
        <i class="las la-arrow-left"></i> {{ __('Back to Bills') }}
    </a>
</div>

<div class="card">
    <div class="card-body table-responsive">
        <table class="table table-bordered aiz-table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Bill No') }}</th>
                    <th>{{ __('Vendor') }}</th>
                    <th>{{ __('Account') }}</th>
                    <th class="text-right">{{ __('Amount') }}</th>
                    <th>{{ __('Reference') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $p)
                <tr>
                    <td>{{ $loop->iteration + ($payments->currentPage()-1)*$payments->perPage() }}</td>
                    <td>{{ optional($p->payment_date)->format('Y-m-d') }}</td>
                    <td>{{ $p->bill?->bill_number }}</td>
                    <td>{{ $p->bill?->vendor?->name }}</td>
                    <td>{{ $p->paymentAccount?->name }}</td>
                    <td class="text-right">{{ number_format($p->amount,2) }}</td>
                    <td>{{ $p->reference }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted p-4">{{ __('No bill payments found.') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-3">
            {{ $payments->links() }}
        </div>
    </div>
</div>
@endsection