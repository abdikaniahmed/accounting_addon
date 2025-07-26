<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Balance Sheet') }}</title>
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
    <p>{{ __('Balance Sheet') }}</p>
    <p>{{ __('As of') }} {{ \Carbon\Carbon::parse($start)->toFormattedDateString() }} - {{ \Carbon\Carbon::parse($end)->toFormattedDateString() }}</p>
</div>

@if($horizontal)
    <table class="table table-bordered table-sm">
        <thead>
        <tr>
            <th class="text-center" colspan="3">{{ __('Assets') }}</th>
            <th class="text-center" colspan="3">{{ __('Liabilities & Equity') }}</th>
        </tr>
        <tr>
            <th>{{ __('Code') }}</th><th>{{ __('Account') }}</th><th class="text-right">{{ __('Balance') }}</th>
            <th>{{ __('Code') }}</th><th>{{ __('Account') }}</th><th class="text-right">{{ __('Balance') }}</th>
        </tr>
        </thead>
        <tbody>
        @php
            $assets = collect($balances['asset'] ?? [])->flatten(1);
            $others = collect($balances)->except('asset')->flatten(2);
            $rows = max($assets->count(), $others->count());
        @endphp
        @for($i = 0; $i < $rows; $i++)
            <tr>
                @php
                    $a = $assets[$i] ?? null;
                    $b = $others[$i] ?? null;
                @endphp
                <td>{{ $a['code'] ?? '' }}</td>
                <td>{{ $a['name'] ?? '' }}</td>
                <td class="text-right">{{ isset($a['balance']) ? number_format($a['balance'], 2) : '' }}</td>

                <td>{{ $b['code'] ?? '' }}</td>
                <td>{{ $b['name'] ?? '' }}</td>
                <td class="text-right">{{ isset($b['balance']) ? number_format($b['balance'], 2) : '' }}</td>
            </tr>
        @endfor
        </tbody>
    </table>
@else
    @foreach($balances as $type => $groups)
        <h5 class="mt-4 text-uppercase">{{ __($type) }}</h5>
        @php $typeTotal = 0; @endphp

        @foreach($groups as $groupName => $accounts)
            <h6 class="text-primary mt-3">{{ $groupName }}</h6>
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>{{ __('Code') }}</th>
                        <th>{{ __('Account Name') }}</th>
                        <th class="text-right">{{ __('Total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @php $groupTotal = 0; @endphp
                    @foreach($accounts as $account)
                        <tr>
                            <td>{{ $account['code'] }}</td>
                            <td>{{ $account['name'] }}</td>
                            <td class="text-right">{{ number_format($account['balance'], 2) }}</td>
                        </tr>
                        @php
                            $groupTotal += $account['balance'];
                            $typeTotal += $account['balance'];
                        @endphp
                    @endforeach
                    <tr>
                        <td colspan="2" class="text-right font-weight-bold">{{ __('Total') }} {{ $groupName }}</td>
                        <td class="text-right font-weight-bold">{{ number_format($groupTotal, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        @endforeach

        <p class="text-right font-weight-bold mt-2">{{ __('Grand Total') }} {{ $type }}: {{ number_format($typeTotal, 2) }}</p>
    @endforeach
@endif

@php
    $totalAssets = collect($balances['asset'] ?? [])->flatten(1)->sum('balance');
    $totalLiabilities = collect($balances['liability'] ?? [])->flatten(1)->sum('balance');
    $netAssets = $totalAssets + $totalLiabilities; // liabilities stored as negative
@endphp

<div class="mt-4 border-top pt-3">
    <h5>{{ __('Summary') }}</h5>
    <table class="table table-bordered summary-table table-sm">
        <tr>
            <th>{{ __('Total Assets') }}</th>
            <td class="text-right">{{ number_format($totalAssets, 2) }}</td>
        </tr>
        <tr>
            <th>{{ __('Total Liabilities') }}</th>
            <td class="text-right">{{ number_format($totalLiabilities, 2) }}</td>
        </tr>
        <tr>
            <th>{{ __('Net Assets') }}</th>
            <td class="text-right font-weight-bold">{{ number_format($netAssets, 2) }}</td>
        </tr>
    </table>
</div>

</body>
</html>