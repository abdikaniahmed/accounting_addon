@extends('admin.partials.master')

@section('title') {{ __('Trial Balance') }} @endsection
@section('accounting_active') sidebar_active @endsection
@section('trial_balance') active @endsection

@section('main-content')
<div class="aiz-titlebar d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">{{ __('Trial Balance') }}</h5>
    <a href="{{ route('admin.accounting.ledger') }}" class="btn btn-light">
        <i class="las la-list"></i> {{ __('Ledger Summary') }}
    </a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form class="row g-2">
            <div class="col-md-3">
                <label class="mb-1">{{ __('Start Date') }}</label>
                <input type="date" name="start_date" value="{{ $start }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="mb-1">{{ __('End Date') }}</label>
                <input type="date" name="end_date" value="{{ $end }}" class="form-control">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <div class="form-check mr-3">
                    <input type="checkbox" name="show_zero" value="1" class="form-check-input" id="showZero"
                        {{ $showZero ? 'checked' : '' }}>
                    <label for="showZero" class="form-check-label">{{ __('Show zero rows') }}</label>
                </div>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-primary mr-2">{{ __('Run') }}</button>
                <a href="{{ route('admin.accounting.trial_balance') }}" class="btn btn-light">{{ __('Reset') }}</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body table-responsive">
        <table class="table table-bordered aiz-table mb-0">
            <thead>
                <tr>
                    <th style="width:120px">{{ __('Code') }}</th>
                    <th>{{ __('Account') }}</th>
                    <th class="text-right">{{ __('Opening (Dr)') }}</th>
                    <th class="text-right">{{ __('Opening (Cr)') }}</th>
                    <th class="text-right">{{ __('Period Dr') }}</th>
                    <th class="text-right">{{ __('Period Cr') }}</th>
                    <th class="text-right">{{ __('Closing (Dr)') }}</th>
                    <th class="text-right">{{ __('Closing (Cr)') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $r)
                <tr>
                    <td>{{ $r->code }}</td>
                    <td>
                        <a
                            href="{{ route('admin.accounting.ledger', ['account_id' => $r->id, 'start_date' => $start, 'end_date' => $end]) }}">
                            {{ $r->name }}
                        </a>
                    </td>
                    <td class="text-right">{{ number_format($r->open_debit,2) }}</td>
                    <td class="text-right">{{ number_format($r->open_credit,2) }}</td>
                    <td class="text-right">{{ number_format($r->mov_debit,2) }}</td>
                    <td class="text-right">{{ number_format($r->mov_credit,2) }}</td>
                    <td class="text-right">{{ number_format($r->close_debit,2) }}</td>
                    <td class="text-right">{{ number_format($r->close_credit,2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted p-4">{{ __('No activity in this period.') }}</td>
                </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="2" class="text-right">{{ __('Totals') }}</th>
                    <th class="text-right">{{ number_format($tot['open_debit'],2) }}</th>
                    <th class="text-right">{{ number_format($tot['open_credit'],2) }}</th>
                    <th class="text-right">{{ number_format($tot['mov_debit'],2) }}</th>
                    <th class="text-right">{{ number_format($tot['mov_credit'],2) }}</th>
                    <th class="text-right">{{ number_format($tot['close_debit'],2) }}</th>
                    <th class="text-right">{{ number_format($tot['close_credit'],2) }}</th>
                </tr>
            </tfoot>
        </table>

        <div class="mt-2 text-muted small">
            {{ __('Note: Closing balance columns should total equally (Dr = Cr). If not, check unbalanced journals.') }}
        </div>
    </div>
</div>
@endsection