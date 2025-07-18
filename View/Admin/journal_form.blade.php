@extends('admin.partials.master')

@section('title')
    {{ __('Add Journal Entry') }}
@endsection

@section('main-content')
<section class="section">
    <div class="section-header">
        <h1>{{ __('Add Journal Entry') }}</h1>
    </div>

    <form action="{{ route('admin.accounting.journals.store') }}" method="POST">
        @csrf
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="form-group col-md-4">
                        <label>{{ __('Journal Number') }}</label>
                        <input type="text" name="journal_number" class="form-control" value="{{ $journal_number }}" readonly>
                    </div>
                    <div class="form-group col-md-4">
                        <label>{{ __('Transaction Date') }}</label>
                        <input type="date" name="date" class="form-control" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label>{{ __('Reference') }}</label>
                        <input type="text" name="reference" class="form-control" placeholder="Enter reference">
                    </div>
                </div>

                <div class="form-group">
                    <label>{{ __('Description') }}</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>

                <hr>
                <h5>{{ __('Accounts') }}</h5>
                
                <div id="account-rows">
                    <div class="row mb-2 account-row">
                        <div class="col-md-4">
                            <select name="accounts[0][account_id]" class="form-control select2" required>
                                <option value="">{{ __('Select Account') }}</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->code_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="number" step="0.01" name="accounts[0][debit]" class="form-control" placeholder="Debit">
                        </div>
                        <div class="col-md-2">
                            <input type="number" step="0.01" name="accounts[0][credit]" class="form-control" placeholder="Credit">
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="accounts[0][description]" class="form-control" placeholder="Note (optional)">
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-danger remove-row">&times;</button>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn btn-secondary mt-2" id="add-row">{{ __('Add Row') }}</button>

                <button type="submit" class="btn btn-primary mt-3">{{ __('Save Journal') }}</button>
            </div>
        </div>
    </form>
</section>
@push('script')
<script>
    let rowCount = 1;

    function reinitializeSelect2(scope = document) {
        $(scope).find('.select2').select2({ width: '100%' });
    }

    document.getElementById('add-row').addEventListener('click', () => {
        const container = document.getElementById('account-rows');
        const originalRow = container.querySelector('.account-row');

        // Clone the row
        const newRow = originalRow.cloneNode(true);

        // Remove select2 DOM artifacts BEFORE appending
        $(newRow).find('.select2-container').remove();

        // Reset inputs and rename them uniquely
        newRow.querySelectorAll('input, select').forEach(el => {
            let name = el.getAttribute('name');
            if (name) {
                el.setAttribute('name', name.replace(/\[\d+]/, `[${rowCount}]`));
            }

            // Reset value
            el.value = '';

            // Reset Select2 options if select
            if (el.tagName === 'SELECT') {
                $(el).removeClass('select2-hidden-accessible').removeAttr('data-select2-id').removeAttr('aria-hidden').removeAttr('tabindex');
            }
        });

        // Append new row before reinitialization
        container.appendChild(newRow);

        // Apply select2 only to the newly added <select>
        reinitializeSelect2(newRow);

        rowCount++;
    });

    // Remove row
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-row')) {
            const rows = document.querySelectorAll('.account-row');
            if (rows.length > 1) {
                e.target.closest('.account-row').remove();
            }
        }
    });

    // Initialize first Select2 on page load
    $(document).ready(function () {
        reinitializeSelect2();
    });
</script>
@endpush


@endsection
