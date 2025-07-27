<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Profit & Loss Report') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: sans-serif; padding: 30px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-uppercase { text-transform: uppercase; }
        .table-sm th, .table-sm td { padding: .3rem; }
        .summary-table { width: 50%; margin-left: auto; }
        .logo { max-height: 70px; margin-bottom: 10px; }
        .border-top { border-top: 1px solid #ddd; }
    </style>
</head>
<body onload="window.print()">

<div class="text-center mb-4">
    <img src="{{ public_path('uploads/company/logo.png') }}" class="logo" alt="Logo">
    <h4>{{ config('app.name', 'Your Company Name') }}</h4>
    <p>{{ __('Profit & Loss Report') }}</p>
    <p>{{ __('From') }} {{ \Carbon\Carbon::parse($start)->toFormattedDateString() }} {{ __('to') }} {{ \Carbon\Carbon::parse($end)->toFormattedDateString() }}</p>
</div>

@php $totalRevenue = 0; $totalExpense = 0; @endphp

<h5 class="text-uppercase mt-4">{{ __('Revenue') }}</h5>
@foreach($report['revenue'] ?? [] as $group => $accounts)
    <h6 class="text-primary mt-3">{{ $group }}</h6>
    <table class="table table-bordered table-sm">
        <thead>
            <tr>
                <th>{{ __('Code') }}</th>
                <th>{{ __('Account') }}</th>
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
                <th>{{ __('Account') }}</th>
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
    <table class="table table-bordered summary-table table-sm">
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
            <td class="text-right font-weight-bold">{{ number_format($totalRevenue - $totalExpense, 2) }}</td>
        </tr>
    </table>
</div>

</body>
</html>