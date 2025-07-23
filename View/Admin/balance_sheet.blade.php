@extends('admin.partials.master')

@section('title') {{ __('Balance Sheet') }} @endsection
@section('accounting_active') sidebar_active @endsection

@section('main-content')
<div class="aiz-titlebar d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">{{ __('Balance Sheet') }}</h5>
</div>

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.accounting.balance_sheet') }}" class="form-inline mb-3">
            <div class="form-group mr-2">
                <input type="date" name="start_date" class="form-control" value="{{ \Carbon\Carbon::parse($start)->format('Y-m-d') }}">
            </div>
            <div class="form-group mr-2">
                <input type="date" name="end_date" class="form-control" value="{{ \Carbon\Carbon::parse($end)->format('Y-m-d') }}">
            </div>
            <div class="form-group form-check mr-2">
                <input type="checkbox" class="form-check-input" name="show_all" id="show_all" value="1" {{ request()->has('show_all') ? 'checked' : '' }}>
                <label class="form-check-label" for="show_all">{{ __('Show All Accounts') }}</label>
            </div>
            <button type="submit" class="btn btn-dark">{{ __('Filter') }}</button>
        </form>

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
                                <td class="text-right">
                                    {{ number_format($account['balance'], 2) }}
                                </td>
                            </tr>
                            @php $groupTotal += $account['balance']; @endphp
                        @endforeach
                        <tr>
                            <td colspan="2" class="text-right font-weight-bold">
                                {{ __('Total') }} {{ $groupName }}
                            </td>
                            <td class="text-right font-weight-bold">
                                {{ number_format($groupTotal, 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            @endforeach
        @endforeach
    </div>
</div>
@endsection
