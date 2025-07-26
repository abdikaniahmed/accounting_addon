@extends('admin.partials.master')

@section('title') {{ __('Profit & Loss Report') }} @endsection
@section('accounting_active') sidebar_active @endsection

@section('profit_loss')
    active
@endsection
@section('main-content')

<div class="aiz-titlebar d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">{{ __('Profit & Loss Report') }}</h5>
    <div>
        <a href="{{ route('admin.accounting.profit_loss.monthly', request()->query()) }}" class="btn btn-sm btn-outline-info">
            <i class="bx bx-calendar"></i> {{ __('Monthly Breakdown') }}
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.accounting.profit_loss') }}" class="form-inline mb-3">
            <div class="form-group mr-2">
                <input type="date" name="start_date" class="form-control"
                       value="{{ \Carbon\Carbon::parse($start)->format('Y-m-d') }}">
            </div>
            <div class="form-group mr-2">
                <input type="date" name="end_date" class="form-control"
                       value="{{ \Carbon\Carbon::parse($end)->format('Y-m-d') }}">
            </div>
            <button type="submit" class="btn btn-dark">{{ __('Filter') }}</button>
        </form>

        @php
            $totalRevenue = 0;
            $totalExpense = 0;
        @endphp

        <h5 class="text-uppercase mt-4">{{ __('Revenue') }}</h5>
        @foreach($report['revenue'] ?? [] as $group => $accounts)
            <h6 class="text-primary mt-3">{{ $group }}</h6>
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>{{ __('Code') }}</th>
                        <th>{{ __('Account Name') }}</th>
                        <th class="text-right">{{ __('Amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @php $groupTotal = 0; @endphp
                    @foreach($accounts as $account)
                        <tr>
                            <td>{{ $account['code'] }}</td>
                            <td>{{ $account['name'] }}</td>
                            <td class="text-right">{{ number_format(-$account['amount'], 2) }}</td>
                        </tr>
                        @php $groupTotal += -$account['amount']; $totalRevenue += -$account['amount']; @endphp
                    @endforeach
                    <tr>
                        <td colspan="2" class="text-right font-weight-bold">{{ __('Total') }} {{ $group }}</td>
                        <td class="text-right font-weight-bold">{{ number_format($groupTotal, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        @endforeach

        <h5 class="text-uppercase mt-4">{{ __('Expenses') }}</h5>
        @foreach($report['expense'] ?? [] as $group => $accounts)
            <h6 class="text-primary mt-3">{{ $group }}</h6>
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>{{ __('Code') }}</th>
                        <th>{{ __('Account Name') }}</th>
                        <th class="text-right">{{ __('Amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @php $groupTotal = 0; @endphp
                    @foreach($accounts as $account)
                        <tr>
                            <td>{{ $account['code'] }}</td>
                            <td>{{ $account['name'] }}</td>
                            <td class="text-right">{{ number_format($account['amount'], 2) }}</td>
                        </tr>
                        @php $groupTotal += $account['amount']; $totalExpense += $account['amount']; @endphp
                    @endforeach
                    <tr>
                        <td colspan="2" class="text-right font-weight-bold">{{ __('Total') }} {{ $group }}</td>
                        <td class="text-right font-weight-bold">{{ number_format($groupTotal, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        @endforeach

        <div class="mt-4 border-top pt-3">
            <h5>{{ __('Summary') }}</h5>
            <table class="table table-bordered w-50 ml-auto">
                <tr>
                    <th>{{ __('Total Revenue') }}</th>
                    <td class="text-right">{{ number_format($totalRevenue, 2) }}</td>
                </tr>
                <tr>
                    <th>{{ __('Total Expenses') }}</th>
                    <td class="text-right">{{ number_format($totalExpense, 2) }}</td>
                </tr>
                <tr>
                    <th>{{ __('Net Profit / Loss') }}</th>
                    <td class="text-right font-weight-bold">
                        {{ number_format($totalRevenue - $totalExpense, 2) }}
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
@endsection