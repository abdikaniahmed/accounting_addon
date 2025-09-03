@extends('admin.partials.master') {{-- or your seller master --}}

@section('title')
{{ $expense->id ? __('Edit Quick Expense') : __('Add Quick Expense') }}
@endsection

@section('main-content')
<div class="aiz-titlebar d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">
        {{ $expense->id ? __('Edit Quick Expense') : __('Add Quick Expense') }}
    </h5>
    <a href="{{ route('seller.accounting.quick_expenses.index') }}" class="btn btn-secondary">
        <i class="las la-arrow-left"></i> {{ __('Back to List') }}
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form
            action="{{ $expense->id ? route('seller.accounting.quick_expenses.update', $expense->id) : route('seller.accounting.quick_expenses.store') }}"
            method="POST" enctype="multipart/form-data">
            @csrf
            @if($expense->id) @method('PUT') @endif

            <div class="row">
                <div class="form-group col-md-6">
                    <label>{{ __('Date') }} <span class="text-danger">*</span></label>
                    <input type="date" name="date" class="form-control"
                        value="{{ old('date', optional($expense->date)->format('Y-m-d') ?? date('Y-m-d')) }}" required>
                </div>

                <div class="form-group col-md-6">
                    <label>{{ __('Expense Account') }} <span class="text-danger">*</span></label>
                    <select name="account_id" class="form-control account-select select2"
                        data-placeholder="{{ __('Select Expense Account') }}" required>
                        <option value="">{{ __('Select Expense Account') }}</option>
                        @foreach($accounts as $id => $name)
                        <option value="{{ $id }}"
                            {{ old('account_id', $expense->account_id) == $id ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>{{ __('Payment Account') }} <span class="text-danger">*</span></label>
                <select name="payment_account_id" class="form-control account-select select2"
                    data-placeholder="{{ __('Select Payment Account') }}" required>
                    <option value="">{{ __('Select Payment Account') }}</option>
                    @foreach($paymentAccounts as $id => $name)
                    <option value="{{ $id }}"
                        {{ old('payment_account_id', $expense->payment_account_id) == $id ? 'selected' : '' }}>
                        {{ $name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>{{ __('Vendor') }}</label>
                <input type="text" name="vendor" class="form-control" value="{{ old('vendor', $expense->vendor) }}">
            </div>

            <div class="form-group">
                <label>{{ __('Reference') }}</label>
                <input type="text" name="reference" class="form-control"
                    value="{{ old('reference', $expense->reference) }}">
            </div>

            <div class="form-group">
                <label>{{ __('Amount') }} <span class="text-danger">*</span></label>
                <input type="number" name="amount" step="0.01" min="0" class="form-control"
                    value="{{ old('amount', $expense->amount) }}" required>
            </div>

            <div class="form-group">
                <label>{{ __('Description') }}</label>
                <textarea name="description" rows="3"
                    class="form-control">{{ old('description', $expense->description) }}</textarea>
            </div>

            <div class="form-group">
                <label>{{ __('Attach Bill / Receipt') }}</label>
                <input type="file" name="bill_file" class="form-control-file">
                @if($expense->bill_file)
                <small class="text-muted d-block mt-1">
                    <a href="{{ uploaded_asset($expense->bill_file) }}"
                        target="_blank">{{ __('View Existing Bill') }}</a>
                </small>
                @endif
            </div>

            <div class="form-group text-right">
                <button type="submit" class="btn btn-primary">
                    {{ $expense->id ? __('Update') : __('Save') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('page-script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const initSelect2 = (root) => {
        if (!window.jQuery || !jQuery.fn.select2) return; // Select2 not loaded
        const els = (root || document).querySelectorAll('.account-select.select2');
        els.forEach(function(el) {
            const $el = jQuery(el);
            if (!$el.hasClass('select2-hidden-accessible')) {
                $el.select2({
                    placeholder: el.dataset.placeholder || '',
                    allowClear: true,
                    width: '100%'
                });
            }
        });
    };

    initSelect2(); // initial
});
</script>
@endpush