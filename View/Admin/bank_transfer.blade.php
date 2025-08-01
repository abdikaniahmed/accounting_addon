@extends('admin.partials.master')

@section('title') {{ __('Bank Balance Transfer') }} @endsection
@section('accounting_active') sidebar_active @endsection
@section('bank_transfer') active @endsection

@section('main-content')
<div class="aiz-titlebar d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">{{ __('Bank Balance Transfer') }}</h5>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.accounting.transfers.store') }}">
            @csrf
            <div class="row">
                <div class="form-group col-md-3">
                    <label>{{ __('Transfer Date') }} <span class="text-danger">*</span></label>
                    <input type="date" name="date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                </div>

                <div class="form-group col-md-3">
                    <label>{{ __('From Account') }} <span class="text-danger">*</span></label>
                    <select name="from_account_id" class="form-control select2" required>
                        <option value="">{{ __('Select Account') }}</option>
                        @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->name }} - {{ $acc->account_number }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-md-3">
                    <label>{{ __('To Account') }} <span class="text-danger">*</span></label>
                    <select name="to_account_id" class="form-control select2" required>
                        <option value="">{{ __('Select Account') }}</option>
                        @foreach($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->name }} - {{ $acc->account_number }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-md-3">
                    <label>{{ __('Amount') }} <span class="text-danger">*</span></label>
                    <input type="number" name="amount" class="form-control" step="0.01" min="0" required>
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-4">
                    <label>{{ __('Reference') }}</label>
                    <input type="text" name="reference" class="form-control">
                </div>
                <div class="form-group col-md-8">
                    <label>{{ __('Description') }}</label>
                    <input type="text" name="description" class="form-control">
                </div>
            </div>

            <div class="mt-3">
                <button class="btn btn-primary">{{ __('Submit Transfer') }}</button>
            </div>
        </form>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h6 class="mb-0">{{ __('Transfer History') }}</h6>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('From Account') }}</th>
                    <th>{{ __('To Account') }}</th>
                    <th>{{ __('Amount') }}</th>
                    <th>{{ __('Reference') }}</th>
                    <th>{{ __('Description') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transfers as $transfer)
                <tr>
                    <td>{{ $transfer->date }}</td>
                    <td>{{ $transfer->fromAccount->name ?? '-' }}</td>
                    <td>{{ $transfer->toAccount->name ?? '-' }}</td>
                    <td>{{ number_format($transfer->amount, 2) }}</td>
                    <td>{{ $transfer->reference }}</td>
                    <td>{{ $transfer->description }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">{{ __('No transfer records found.') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('script')
<script>
$('.select2').select2();
</script>
@endpush