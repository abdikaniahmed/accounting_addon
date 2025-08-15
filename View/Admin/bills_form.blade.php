@extends('admin.partials.master')

@section('title') {{ isset($bill) ? __('Edit Bill') : __('Record Bill') }} @endsection
@section('accounting_active') sidebar_active @endsection
@section('bills') active @endsection

@section('main-content')
<div class="aiz-titlebar d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">{{ isset($bill) ? __('Edit Bill') : __('Record Bill (A/P)') }}</h5>
    <a href="{{ route('admin.accounting.bills.index') }}" class="btn btn-secondary">
        <i class="las la-arrow-left"></i> {{ __('Back to List') }}
    </a>
</div>

<form
    action="{{ isset($bill) ? route('admin.accounting.bills.update',$bill->id) : route('admin.accounting.bills.store') }}"
    method="POST" enctype="multipart/form-data">
    @csrf
    @if(isset($bill)) @method('PUT') @endif

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="form-group col-md-4">
                    <label>{{ __('Vendor') }} <span class="text-danger">*</span></label>
                    <select name="vendor_id" class="form-control select2" required>
                        <option value="">{{ __('Select Vendor') }}</option>
                        @foreach($vendors as $id => $name)
                        <option value="{{ $id }}"
                            {{ old('vendor_id', $bill->vendor_id ?? '') == $id ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-md-3">
                    <label>{{ __('Bill Date') }} <span class="text-danger">*</span></label>
                    <input type="date" name="bill_date" class="form-control"
                        value="{{ old('bill_date', isset($bill->bill_date) ? \Illuminate\Support\Carbon::parse($bill->bill_date)->format('Y-m-d') : now()->format('Y-m-d')) }}"
                        required>
                </div>

                <div class="form-group col-md-3">
                    <label>{{ __('Bill No.') }} <span class="text-danger">*</span></label>
                    <input type="text" name="bill_number" class="form-control"
                        value="{{ old('bill_number', $bill->bill_number ?? '') }}" placeholder="BILL-0001" required>
                </div>

                <div class="form-group col-md-2">
                    <label>{{ __('Attachment') }}</label>
                    <input type="file" name="bill_file" class="form-control">
                </div>
            </div>

            <hr>
            <h6 class="mb-2">{{ __('Items (Expense lines)') }}</h6>

            <div id="item-rows">
                @php
                $rows = old('items', isset($bill)
                ? $bill->items->map(fn($i)=>[
                'account_id' => $i->account_id,
                'description' => $i->description,
                'quantity' => $i->quantity ?? $i->qty ?? '',
                'unit_price' => $i->unit_price,
                ])->toArray()
                : [['account_id'=>'','description'=>'','quantity'=>'','unit_price'=>'']]
                );
                @endphp

                @foreach($rows as $i => $row)
                <div class="row item-row align-items-start mb-2">
                    <div class="col-md-4">
                        <label class="d-md-none">{{ __('Expense Account') }}</label>
                        <select name="items[{{ $i }}][account_id]" class="form-control select2" required>
                            <option value="">{{ __('Select Expense Account') }}</option>
                            @foreach($expenseAccounts as $id => $name)
                            <option value="{{ $id }}"
                                {{ (string)($row['account_id'] ?? '') === (string)$id ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="d-md-none">{{ __('Description') }}</label>
                        <input type="text" name="items[{{ $i }}][description]" class="form-control"
                            value="{{ $row['description'] ?? '' }}" placeholder="{{ __('Optional memo') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="d-md-none">{{ __('Qty') }}</label>
                        <input type="number" step="0.0001" min="0" name="items[{{ $i }}][quantity]"
                            class="form-control qty-input" value="{{ $row['quantity'] ?? '' }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="d-md-none">{{ __('Unit Price') }}</label>
                        <input type="number" step="0.01" min="0" name="items[{{ $i }}][unit_price]"
                            class="form-control price-input" value="{{ $row['unit_price'] ?? '' }}" required>
                    </div>
                    <div class="col-md-1 d-flex align-items-start">
                        <button type="button" class="btn btn-outline-danger ml-2 remove-row"
                            title="{{ __('Remove') }}">×</button>
                    </div>
                </div>
                @endforeach
            </div>

            <button type="button" class="btn btn-sm btn-outline-secondary mb-3" id="add-row">
                <i class="bx bx-plus"></i> {{ __('Add Line') }}
            </button>

            {{-- Inert template (ignored by form validation) --}}
            <template id="bill-row-template">
                <div class="row item-row align-items-start mb-2">
                    <div class="col-md-4">
                        <label class="d-md-none">{{ __('Expense Account') }}</label>
                        <select name="items[__i__][account_id]" class="form-control select2" required>
                            <option value="">{{ __('Select Expense Account') }}</option>
                            @foreach($expenseAccounts as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="d-md-none">{{ __('Description') }}</label>
                        <input type="text" name="items[__i__][description]" class="form-control"
                            placeholder="{{ __('Optional memo') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="d-md-none">{{ __('Qty') }}</label>
                        <input type="number" step="0.0001" min="0" name="items[__i__][quantity]"
                            class="form-control qty-input" required>
                    </div>
                    <div class="col-md-2">
                        <label class="d-md-none">{{ __('Unit Price') }}</label>
                        <input type="number" step="0.01" min="0" name="items[__i__][unit_price]"
                            class="form-control price-input" required>
                    </div>
                    <div class="col-md-1 d-flex align-items-start">
                        <button type="button" class="btn btn-outline-danger ml-2 remove-row"
                            title="{{ __('Remove') }}">×</button>
                    </div>
                </div>
            </template>

            <div class="row">
                <div class="col-md-6">
                    <label>{{ __('Notes') }}</label>
                    <textarea name="notes" rows="3"
                        class="form-control">{{ old('notes', $bill->notes ?? '') }}</textarea>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm mb-0">
                        <tr>
                            <th class="text-right">{{ __('Total') }}</th>
                            <td class="text-right w-25"><span id="totalDisplay">0.00</span></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="mt-3">
                <button class="btn btn-primary">{{ isset($bill) ? __('Update Bill') : __('Save Bill') }}</button>
                <a href="{{ route('admin.accounting.bills.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
            </div>
        </div>
    </div>
</form>
@endsection

@push('script')
<script>
(function() {
    // Formatting-proof (your request):
    let rowCount = parseInt("{{ count($rows) }}", 10);
    const wrap = document.getElementById('item-rows');
    const tplEl = document.getElementById('bill-row-template');

    function initSelect2(scope) {
        if (window.$ && $.fn && $.fn.select2) {
            $(scope).find('.select2').select2({
                width: '100%'
            });
        }
    }

    function recalc() {
        let total = 0;
        document.querySelectorAll('.item-row').forEach(function(row) {
            const q = parseFloat(row.querySelector('.qty-input')?.value || 0);
            const p = parseFloat(row.querySelector('.price-input')?.value || 0);
            if (!isNaN(q) && !isNaN(p)) total += q * p;
        });
        const el = document.getElementById('totalDisplay');
        if (el) el.textContent = total.toFixed(2);
    }

    function addRow() {
        // pull HTML from <template> and replace __i__
        const html = tplEl.innerHTML.trim().replace(/__i__/g, String(rowCount));
        wrap.insertAdjacentHTML('beforeend', html);
        const newRow = wrap.lastElementChild;
        initSelect2(newRow);
        rowCount++;
        recalc();
    }

    document.getElementById('add-row').addEventListener('click', addRow);

    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('qty-input') || e.target.classList.contains('price-input')) {
            recalc();
        }
    });

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-row')) {
            const rows = document.querySelectorAll('.item-row');
            if (rows.length > 1) e.target.closest('.item-row').remove();
            recalc();
        }
    });

    // init existing rows & starting total
    initSelect2(document);
    recalc();
})();
</script>
@endpush