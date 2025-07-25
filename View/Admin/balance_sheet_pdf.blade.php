<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Balance Sheet') }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            padding: 10px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-primary { color: #007bff; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td {
            border: 1px solid #000;
            padding: 6px;
        }
        .font-weight-bold { font-weight: bold; }
        h4, h5, h6 { margin: 5px 0; }
        .mt-3 { margin-top: 1rem; }
        .mt-4 { margin-top: 1.5rem; }
        img {
            display: block;
            margin: 0 auto;
        }

    </style>
</head>
<body>
    <div class="text-center mb-4">
        <img src="{{ asset('uploads/company/logo.png') }}" alt="Company Logo" height="70" style="margin-bottom: 10px;">
        <h4>{{ config('app.name', 'Your Company Name') }}</h4>
        <p>{{ __('Balance Sheet') }}</p>
        <p>{{ __('As of') }} {{ \Carbon\Carbon::parse($start)->toFormattedDateString() }} - {{ \Carbon\Carbon::parse($end)->toFormattedDateString() }}</p>
    </div>
    @foreach($balances as $type => $groups)
            <h5 class="mt-4">{{ __($type) }}</h5>
        @foreach($groups as $groupName => $accounts)
            <h6 class="text-primary mt-3">{{ $groupName }}</h6>
            <table class="table">
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