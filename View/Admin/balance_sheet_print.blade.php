<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Balance Sheet') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: sans-serif;
            padding: 30px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .text-uppercase {
            text-transform: uppercase;
        }
        table {
            width: 100%;
            margin-bottom: 20px;
        }
        th, td {
            padding: 8px;
        }
        .font-weight-bold {
            font-weight: bold;
        }
        .mt-4 {
            margin-top: 1.5rem;
        }
        .mt-3 {
            margin-top: 1rem;
        }
        .table-sm th, .table-sm td {
            padding: .3rem;
        }
        img {
            display: block;
            margin: 0 auto;
        }
    </style>
</head>
<body onload="window.print()">
    <div class="text-center mb-4">
        <img src="{{ asset('uploads/company/logo.png') }}" alt="Company Logo" height="70" style="margin-bottom: 10px;">
        <h4>{{ config('app.name', 'Your Company Name') }}</h4>
        <p>{{ __('Balance Sheet') }}</p>
        <p>{{ __('As of') }} {{ \Carbon\Carbon::parse($start)->toFormattedDateString() }} - {{ \Carbon\Carbon::parse($end)->toFormattedDateString() }}</p>
    </div>

    @foreach($balances as $type => $groups)
        <h5 class="mt-4 text-uppercase">{{ __($type) }}</h5>
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
                        @php $groupTotal += $account['balance']; @endphp
                    @endforeach
                    <tr>
                        <td colspan="2" class="text-right font-weight-bold">{{ __('Total') }} {{ $groupName }}</td>
                        <td class="text-right font-weight-bold">{{ number_format($groupTotal, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        @endforeach
    @endforeach
        @php
        $totalAssets = 0;
        $totalLiabilities = 0;

        if (isset($balances['asset'])) {
            foreach ($balances['asset'] as $accounts) {
                foreach ($accounts as $acc) {
                    $totalAssets += $acc['balance'];
                }
            }
        }

        if (isset($balances['liability'])) {
            foreach ($balances['liability'] as $accounts) {
                foreach ($accounts as $acc) {
                    $totalLiabilities += $acc['balance'];
                }
            }
        }

        $netAssets = $totalAssets - $totalLiabilities;
    @endphp
    <div class="mt-4 border-top pt-3">
        <h5 class="text-dark">{{ __('Summary') }}</h5>
        <table class="table table-bordered w-50 ml-auto">
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
