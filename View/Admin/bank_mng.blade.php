@extends('admin.partials.master')

@section('title') {{ __('Manage Bank Account') }} @endsection
@section('accounting_active') sidebar_active @endsection
@section('bank_account') active @endsection

@section('main-content')
<div class="aiz-titlebar d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">{{ __('Manage Bank Account') }}</h5>
    <div>
        <a href="{{ route('admin.accounting.transfers.index') }}" class="btn btn-sm btn-secondary mr-1">
            <i class="bx bx-transfer"></i> {{ __('Transfers') }}
        </a>
        <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addBankModal">
            <i class="bx bx-plus"></i> {{ __('Add New') }}
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-bordered table-striped table-sm mb-0">
            <thead>
                <tr>
                    <th>{{ __('Chart of Account') }}</th>
                    <th>{{ __('Bank Name') }}</th>
                    <th>{{ __('Account Number') }}</th>
                    <th>{{ __('Holder Name') }}</th>
                    <th>{{ __('Contact') }}</th>
                    <th>{{ __('Address') }}</th>
                    <th class="text-right">{{ __('Current Balance') }}</th>
                    <th class="text-center">{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bankAccounts as $bank)
                <tr id="row_{{ $bank->id }}">
                    <td>{{ $bank->account->code ?? '' }} - {{ $bank->account->name ?? '' }}</td>
                    <td>{{ $bank->bank_name }}</td>
                    <td>{{ $bank->account_number }}</td>
                    <td>{{ $bank->holder_name }}</td>
                    <td>{{ $bank->contact_number }}</td>
                    <td>{{ $bank->address }}</td>
                    <td class="text-right">
                        <a href="{{ route('admin.accounting.ledger', ['account_id' => $bank->account_id]) }}">
                            {{ number_format($bank->calculated_balance, 2) }}
                        </a>
                    </td>
                    <td class="text-center">
                        {{-- SweetAlert delete (ajax) --}}
                        <button class="btn btn-sm btn-danger"
                            onclick="delete_row('accounting/bank-accounts/', {{ $bank->id }})" data-toggle="tooltip"
                            title="{{ __('Delete') }}">
                            <i class="bx bx-trash"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted">{{ __('No bank accounts found.') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="addBankModal" tabindex="-1" role="dialog" aria-labelledby="addBankLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form id="bankForm" method="POST" action="{{ route('admin.accounting.bank_accounts.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Create New Bank Account') }}</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body row">
                    <div class="form-group col-md-6">
                        <label>{{ __('Select Account') }} *</label>
                        <select name="account_id" class="form-control select2" required>
                            <option value="">{{ __('Select Account') }}</option>
                            @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label>{{ __('Bank Holder Name') }}</label>
                        <input type="text" name="bank_holder_name" class="form-control">
                    </div>
                    <div class="form-group col-md-6">
                        <label>{{ __('Bank Name') }}</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label>{{ __('Account Number') }}</label>
                        <input type="text" name="account_number" class="form-control">
                    </div>
                    <div class="form-group col-md-6">
                        <label>{{ __('Opening Balance') }}</label>
                        <input type="number" name="opening_balance" class="form-control" step="0.01">
                    </div>
                    <div class="form-group col-md-6">
                        <label>{{ __('Contact Number') }}</label>
                        <input type="text" name="contact_number" class="form-control" placeholder="+25263xxxxxxx">
                    </div>
                    <div class="form-group col-md-12">
                        <label>{{ __('Bank Address') }}</label>
                        <textarea name="address" class="form-control" rows="2"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Create') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('script')
{{-- SweetAlert delete helper --}}
@include('admin.common.delete-ajax')
@endpush