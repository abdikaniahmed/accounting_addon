@extends('admin.partials.master')

@section('title') {{ __('Profit & Loss - Monthly Breakdown') }} @endsection
@section('accounting_active') sidebar_active @endsection
@section('profit_loss') active @endsection

@section('main-content')
<div class="aiz-titlebar d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">{{ __('Profit & Loss - Monthly Breakdown') }}</h5>
    <a href="{{ route('admin.accounting.profit_loss') }}" class="btn btn-sm btn-outline-primary">
        <i class="bx bx-arrow-back"></i> {{ __('Back to Summary') }}
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.accounting.profit_loss.monthly') }}" class="form-inline mb-3">
            <input type="date" name="start_date" class="form-control mr-2" value="{{ $start->format('Y-m-d') }}">
            <input type="date" name="end_date" class="form-control mr-2" value="{{ $end->format('Y-m-d') }}">
            <button type="submit" class="btn btn-dark">{{ __('Filter') }}</button>
        </form>

        @foreach($monthlyReport as $type => $groups)
            <h5 class="mt-4 text-uppercase">{{ __($type) }}</h5>
            @foreach($groups as $group => $accounts)
                <h6 class="text-primary">{{ $group }}</h6>
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>{{ __('Account') }}</th>
                            @foreach($months as $key => $label)
                                <th class="text-right">{{ $label }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($accounts as $account => $amounts)
                            <tr>
                                <td>{{ $account }}</td>
                                @foreach($months as $key => $label)
                                    <td class="text-right">
                                        {{ number_format((float) ($amounts[$key] ?? 0), 2) }}
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach
        @endforeach
    </div>
</div>

<div class="mt-5">
    <h5>{{ __('Monthly Net Profit Overview') }}</h5>
    <canvas id="monthlyProfitChart" height="90"></canvas>
</div>
@endsection

@push('script')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const chart = document.getElementById('monthlyProfitChart').getContext('2d');
    const monthKeys = {!! json_encode(array_keys($months)) !!};    // Y-m
    const monthLabels = {!! json_encode(array_values($months)) !!}; // Jan, Feb...

    const revenueData = @json($monthlyReport['revenue'] ?? []);
    const expenseData = @json($monthlyReport['expense'] ?? []);

    const profits = monthKeys.map(key => {
        let revenue = 0;
        let expense = 0;

        Object.values(revenueData).forEach(accounts => {
            Object.values(accounts).forEach(amounts => {
                revenue += parseFloat(amounts[key] ?? 0);
            });
        });

        Object.values(expenseData).forEach(accounts => {
            Object.values(accounts).forEach(amounts => {
                expense += parseFloat(amounts[key] ?? 0);
            });
        });

        return (revenue - expense).toFixed(2);
    });

    new Chart(chart, {
        type: 'bar',
        data: {
            labels: monthLabels,
            datasets: [{
                label: '{{ __("Net Profit") }}',
                data: profits,
                backgroundColor: '#4e73df'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>
@endpush
