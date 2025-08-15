@extends('admin.partials.master')

@section('title') {{ __('Pay Bill') }} @endsection
@section('accounting_active') sidebar_active @endsection

@section('main-content')
<div class="aiz-titlebar d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">{{ __('Pay Bill') }}</h5>
    <a href="{{ route('admin.accounting.bills.index') }}" class="btn btn-secondary">
        <i class="las la-arrow-left"></i> {{ __('Back to List') }}
    </a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <h6 class="mb-2">{{ __('Bill Summary') }}</h6>
                <p class="mb-1"><strong>{{ __('Bill No') }}:</strong> {{ $bill->bill_number }}</p>
                <p class="mb-1"><strong>{{ __('Vendor') }}:</strong> {{ $bill->vendor?->name }}</p>
                <p class="mb-1"><strong>{{ __('Bill Date') }}:</strong>
                    {{ optional($bill->bill_date)->format('Y-m-d') }}</p>
                <p class="mb-1"><strong>{{ __('Notes') }}:</strong> {{ $bill->notes ?? '-' }}</p>
            </div>
            <div class="col-md-4">
                <table class="table table-sm">
                    <tr>
                        <th class="text-right">{{ __('Total') }}</th>
                        <td class="text-right">{{ number_format($bill->total_amount,2) }}</td>
                    </tr>
                    <tr>
                        <th class="text-right">{{ __('Paid') }}</th>
                        <td class="text-right">{{ number_format($paid,2) }}</td>
                    </tr>
                    <tr>
                        <th class="text-right">{{ __('Due') }}</th>
                        <td class="text-right font-weight-bold">{{ number_format($due,2) }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0">{{ __('Make a Payment') }}</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.accounting.bills.pay.store', $bill->id) }}" method="POST">
            @csrf
            <div class="row">
                <div class="form-group col-md-3">
                    <label>{{ __('Payment Date') }} <span class="text-danger">*</span></label>
                    {{-- name MUST be payment_date to match controller --}}
                    <input type="date" name="payment_date" class="form-control" value="{{ now()->format('Y-m-d') }}"
                        required>
                </div>
                <div class="form-group col-md-4">
                    <label>{{ __('Payment Account (Cash/Bank)') }} <span class="text-danger">*</span></label>
                    <select name="payment_account_id" class="form-control select2" required>
                        <option value="">{{ __('Select Account') }}</option>
                        @foreach($moneyAccounts as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label>{{ __('Amount') }} <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0.01" max="{{ $due }}" name="amount" class="form-control"
                        required>
                </div>
                <div class="form-group col-md-3">
                    <label>{{ __('Reference') }}</label>
                    <input type="text" name="reference" class="form-control" placeholder="#PAY-...">
                </div>
                <div class="form-group col-md-12">
                    <label>{{ __('Description / Memo') }}</label>
                    <input type="text" name="description" class="form-control" placeholder="{{ __('Optional') }}">
                </div>
            </div>
            <button class="btn btn-success" {{ $due <= 0 ? 'disabled' : '' }}>
                <i class="bx bx-check"></i> {{ __('Record Payment') }}
            </button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="mb-0">{{ __('Payment History') }}</h6>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-bordered mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Account') }}</th>
                    <th>{{ __('Reference') }}</th>
                    <th>{{ __('Description') }}</th>
                    <th class="text-right">{{ __('Amount') }}</th>
                    <th class="text-center">{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bill->payments as $p)
                <tr id="row_payment_{{ $p->id }}">
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ optional($p->date)->format('Y-m-d') }}</td>
                    <td>{{ $p->paymentAccount?->name ?? '-' }}</td>
                    <td>{{ $p->reference }}</td>
                    <td>{{ $p->description }}</td>
                    <td class="text-right">{{ number_format($p->amount,2) }}</td>
                    <td class="text-center">
                        <a href="javascript:void(0);"
                            onclick="delete_row('accounting/bills/{{ $bill->id }}/payments/', {{ $p->id }})"
                            class="btn btn-sm btn-outline-danger" title="{{ __('Delete') }}">
                            <i class="bx bx-trash"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted">{{ __('No payments yet.') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('script')
@include('admin.common.delete-ajax')
<script>
if (window.$) $('.select2').select2({
    width: '100%'
});
</script>
@endpush