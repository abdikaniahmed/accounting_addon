@extends('admin.partials.master')

@section('title')
{{ isset($entry) ? __('Edit Journal Entry') : __('Add Journal Entry') }}
@endsection

@section('main-content')
<section class="section">
    <div class="section-header">
        <h1>{{ __('Journal Entry') }}</h1>
    </div>

    <form
        action="{{ isset($entry) ? route('seller.accounting.journals.update', $entry->id) : route('seller.accounting.journals.store') }}"
        method="POST">
        @csrf
        @if(isset($entry))
        @method('PUT')
        @endif
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="form-group col-md-4">
                        <label>{{ __('Journal Number') }}</label>
                        <input type="text" name="journal_number" class="form-control"
                            value="{{ old('journal_number', $entry->journal_number ?? $journal_number ?? '') }}"
                            readonly>
                    </div>
                    <div class="form-group col-md-4">
                        <label>{{ __('Transaction Date') }}</label>
                        <input type="date" name="date" class="form-control"
                            value="{{ old('date', isset($entry) ? $entry->date->format('Y-m-d') : '') }}" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label>{{ __('Reference') }}</label>
                        <input type="text" name="reference" class="form-control"
                            value="{{ old('reference', $entry->reference ?? '') }}" placeholder="Enter reference">
                    </div>
                </div>

                <div class="form-group">
                    <label>{{ __('Description') }}</label>
                    <textarea name="description" class="form-control"
                        rows="3">{{ old('description', $entry->description ?? '') }}</textarea>
                </div>

                <hr>
                <h5>{{ __('Accounts') }}</h5>

                <div id="account-rows">
                    @php
                    $rows = old('accounts', isset($entry) ? $entry->items : [['account_id' => '', 'debit' => '',
                    'credit' => '', 'description' => '']]);
                    @endphp
                    @foreach($rows as $i => $item)
                    <div class="row mb-2 account-row">
                        <div class="col-md-4">
                            <select name="accounts[{{ $i }}][account_id]" class="form-control select2" required>
                                <option value="">{{ __('Select Account') }}</option>
                                @foreach($accounts as $account)
                                <option value="{{ $account->id }}"
                                    {{ old("accounts.{$i}.account_id", $item['account_id'] ?? ($item->account_id ?? '')) == $account->id ? 'selected' : '' }}>
                                    {{ $account->code_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="number" step="0.01" name="accounts[{{ $i }}][debit]" class="form-control"
                                placeholder="Debit"
                                value="{{ old("accounts.{$i}.debit", $item['debit'] ?? ($item->type == 'debit' ? $item->amount : '') ?? '') }}">
                        </div>
                        <div class="col-md-2">
                            <input type="number" step="0.01" name="accounts[{{ $i }}][credit]" class="form-control"
                                placeholder="Credit"
                                value="{{ old("accounts.{$i}.credit", $item['credit'] ?? ($item->type == 'credit' ? $item->amount : '') ?? '') }}">
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="accounts[{{ $i }}][description]" class="form-control"
                                placeholder="Note (optional)"
                                value="{{ old("accounts.{$i}.description", $item['description'] ?? $item->description ?? '') }}">
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-danger remove-row">&times;</button>
                        </div>
                    </div>
                    @endforeach
                </div>

                <button type="button" class="btn btn-secondary mt-2" id="add-row">{{ __('Add Row') }}</button>

                <div class="mt-4">
                    <button type="submit"
                        class="btn btn-primary mt-3">{{ isset($entry) ? __('Update Journal') : __('Save Journal') }}</button>
                    <a href="{{ route('seller.accounting.journals') }}"
                        class="btn btn-light mt-3">{{ __('Cancel') }}</a>
                </div>
            </div>
        </div>
    </form>
    @if ($errors->any())
    <div class="alert alert-danger mt-3">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if (session('error'))
    <div class="alert alert-danger mt-3">
        {{ session('error') }}
    </div>
    @endif

    @if (session('success'))
    <div class="alert alert-success mt-3">
        {{ session('success') }}
    </div>
    @endif

</section>
@endsection

@push('script')
<script>
let rowCount = {
    {
        count($rows)
    }
};

function reinitializeSelect2(scope = document) {
    $(scope).find('.select2').select2({
        width: '100%'
    });
}

document.getElementById('add-row').addEventListener('click', () => {
    const container = document.getElementById('account-rows');
    const originalRow = container.querySelector('.account-row');
    const newRow = originalRow.cloneNode(true);

    $(newRow).find('.select2-container').remove();

    newRow.querySelectorAll('input, select').forEach(el => {
        const name = el.getAttribute('name');
        if (name) el.setAttribute('name', name.replace(/\[\d+]/, `[${rowCount}]`));
        el.value = '';
        if (el.tagName === 'SELECT') {
            $(el).removeClass('select2-hidden-accessible')
                .removeAttr('data-select2-id')
                .removeAttr('aria-hidden')
                .removeAttr('tabindex');
        }
    });

    container.appendChild(newRow);
    reinitializeSelect2(newRow);
    rowCount++;
});

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-row')) {
        const rows = document.querySelectorAll('.account-row');
        if (rows.length > 1) {
            e.target.closest('.account-row').remove();
        }
    }
});

$(document).ready(function() {
    reinitializeSelect2();
});
</script>
@endpush