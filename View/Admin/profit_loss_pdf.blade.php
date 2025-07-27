<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Profit & Loss Report') }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            padding: 20px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-primary { color: #007bff; }
        .font-weight-bold { font-weight: bold; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .table th, .table td {
            border: 1px solid #000;
            padding: 6px;
        }
        h4, h5, h6 {
            margin: 5px 0;
        }
        .summary-table {
            width: 50%;
            margin-left: auto;
            margin-top: 20px;
        }
        img {
            height: 60px;
            margin-bottom: 8px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
    </style>
</head>
<body>
<div class="text-center mb-3">
    <img src="{{ public_path('uploads/company/logo.png') }}" alt="Logo">
    <h4>{{ config('app.name', 'Your Company Name') }}</h4>
    <p>{{ __('Profit & Loss Report') }}</p>
    <p>{{ __('From') }} {{ \Carbon\Carbon::parse($start)->toFormattedDateString() }} {{ __('to') }} {{ \Carbon\Carbon::parse($end)->toFormattedDateString() }}</p>
</div>

@php $totalRevenue = 0; $totalExpense = 0; @endphp

<h5 class="text-uppercase mt-4">{{ __('Revenue') }}</h5>
@foreach($report['revenue'] ?? [] as $group => $accounts)
    <h6 class="text-primary">{{ $group }}</h6>
    <table class="table">
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
    <h6 class="text-primary">{{ $group }}</h6>
    <table class="table">
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

<div class="mt-4">
    <h5>{{ __('Summary') }}</h5>
    <table class="table summary-table">
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