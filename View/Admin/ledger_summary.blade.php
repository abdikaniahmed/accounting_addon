@extends('admin.partials.master')

@section('title')
    {{ __('Ledger Summary') }}
@endsection

@section('ledger_summary')
    active
@endsection

@section('main-content')
<section class="section">
    <div class="section-header">
        <h1>{{ __('Ledger Summary') }}</h1>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.accounting.ledger') }}">
                <div class="row">
                    <div class="form-group col-md-4">
                        <label>{{ __('Start Date') }}</label>
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>{{ __('End Date') }}</label>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>{{ __('Account') }}</label>
                        <select name="account_id" class="form-control select2">
                            <option value="">{{ __('All Accounts') }}</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}" {{ request('account_id') == $account->id ? 'selected' : '' }}>
                                    {{ $account->code }} - {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button class="btn btn-primary" type="submit">{{ __('Filter') }}</button>
            </form>
        </div>
    </div>

    @foreach($ledgers as $ledger)
        <div class="card mt-3">
            <div class="card-header">
                <h4>{{ $ledger['account']->code }} - {{ $ledger['account']->name }}</h4>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered m-0">
                    <thead>
                        <tr>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Reference') }}</th>
                            <th>{{ __('Description') }}</th>
                            <th>{{ __('Debit') }}</th>
                            <th>{{ __('Credit') }}</th>
                            <th>{{ __('Balance') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $balance = 0; @endphp
                        @forelse($ledger['transactions'] as $txn)
                            @php
                                $amount = $txn->type === 'debit' ? $txn->amount : -$txn->amount;
                                $balance += $amount;
                            @endphp
                            <tr>
                                <td>{{ $txn->journalEntry->date }}</td>
                                <td>{{ $txn->journalEntry->journal_number }}</td>
                                <td>{{ $txn->journalEntry->description }}</td>
                                <td>{{ $txn->type === 'debit' ? number_format($txn->amount, 2) : '' }}</td>
                                <td>{{ $txn->type === 'credit' ? number_format($txn->amount, 2) : '' }}</td>
                                <td>{{ number_format($balance, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">{{ __('No transactions found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
</section>
@endsection

@push('script')
<script>
    $(document).ready(function () {
        $('.select2').select2();
    });
</script>
@endpush
