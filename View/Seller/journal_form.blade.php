{{-- resources/views/addons/accountingSeller/journal_form.blade.php --}}
@extends('admin.partials.master') {{-- or your seller master --}}

@section('title', isset($entry) ? __('Edit Journal Entry') : __('New Journal Entry'))

@section('main-content')
<section class="section">
    <div class="section-header">
        <h1>{{ isset($entry) ? __('Edit Journal Entry') : __('New Journal Entry') }}</h1>
    </div>

    {{-- Flash & validation errors --}}
    @if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST"
        action="{{ isset($entry) ? route('seller.accounting.journals.update', $entry->id) : route('seller.accounting.journals.store') }}">
        @csrf
        @if(isset($entry)) @method('PUT') @endif

        <div class="card">
            <div class="card-body">

                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label class="mb-1">{{ __('Journal Number') }}</label>
                        <input type="text" name="journal_number" class="form-control"
                            value="{{ old('journal_number', $entry->journal_number ?? $journal_number ?? '') }}"
                            required>
                    </div>
                    <div class="form-group col-md-3">
                        <label class="mb-1">{{ __('Date') }}</label>
                        <input type="date" name="date" class="form-control"
                            value="{{ old('date', isset($entry) ? $entry->date : date('Y-m-d')) }}" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label class="mb-1">{{ __('Reference') }}</label>
                        <input type="text" name="reference" class="form-control"
                            value="{{ old('reference', $entry->reference ?? '') }}">
                    </div>
                    <div class="form-group col-md-3">
                        <label class="mb-1">{{ __('Description') }}</label>
                        <input type="text" name="description" class="form-control"
                            value="{{ old('description', $entry->description ?? '') }}">
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="mb-0">{{ __('Lines') }}</h5>
                    <button type="button" id="add-row" class="btn btn-sm btn-primary">
                        {{ __('Add Row') }}
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead>
                            <tr>
                                <th style="width:34%">{{ __('Account') }}</th>
                                <th style="width:18%">{{ __('Debit') }}</th>
                                <th style="width:18%">{{ __('Credit') }}</th>
                                <th>{{ __('Note') }}</th>
                                <th style="width:60px"></th>
                            </tr>
                        </thead>

                        <tbody id="lines-body"
                            data-index="{{ isset($entry) ? $entry->journalItems->count() : (is_array(old('accounts')) ? count(old('accounts')) : 1) }}">

                            {{-- Re-populate from old() on validation error --}}
                            @if (is_array(old('accounts')))
                            @foreach(old('accounts') as $i => $row)
                            <tr>
                                <td>
                                    <select name="accounts[{{ $i }}][account_id]"
                                        class="form-control account-select select2"
                                        data-placeholder="{{ __('Select account') }}" required>
                                        <option value="">{{ __('Select account') }}</option>
                                        @foreach($accounts as $acc)
                                        <option value="{{ $acc->id }}"
                                            {{ (int)($row['account_id'] ?? 0) === (int)$acc->id ? 'selected' : '' }}>
                                            {{ $acc->code_name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" inputmode="decimal"
                                        class="form-control debit-input" name="accounts[{{ $i }}][debit]"
                                        value="{{ $row['debit'] ?? '' }}">
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" inputmode="decimal"
                                        class="form-control credit-input" name="accounts[{{ $i }}][credit]"
                                        value="{{ $row['credit'] ?? '' }}">
                                </td>
                                <td>
                                    <input type="text" class="form-control" name="accounts[{{ $i }}][description]"
                                        value="{{ $row['description'] ?? '' }}">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-row"
                                        aria-label="Remove">×</button>
                                </td>
                            </tr>
                            @endforeach

                            {{-- Editing existing entry --}}
                            @elseif(isset($entry))
                            @foreach($entry->journalItems as $i => $item)
                            <tr>
                                <td>
                                    <select name="accounts[{{ $i }}][account_id]"
                                        class="form-control account-select select2"
                                        data-placeholder="{{ __('Select account') }}" required>
                                        <option value="">{{ __('Select account') }}</option>
                                        @foreach($accounts as $acc)
                                        <option value="{{ $acc->id }}"
                                            {{ (int)$item->account_id === (int)$acc->id ? 'selected' : '' }}>
                                            {{ $acc->code_name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" inputmode="decimal"
                                        class="form-control debit-input" name="accounts[{{ $i }}][debit]"
                                        value="{{ $item->type === 'debit' ? $item->amount : '' }}">
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" inputmode="decimal"
                                        class="form-control credit-input" name="accounts[{{ $i }}][credit]"
                                        value="{{ $item->type === 'credit' ? $item->amount : '' }}">
                                </td>
                                <td>
                                    <input type="text" class="form-control" name="accounts[{{ $i }}][description]"
                                        value="{{ $item->memo ?? '' }}">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-row"
                                        aria-label="Remove">×</button>
                                </td>
                            </tr>
                            @endforeach

                            {{-- New entry default row --}}
                            @else
                            <tr>
                                <td>
                                    <select name="accounts[0][account_id]" class="form-control account-select select2"
                                        data-placeholder="{{ __('Select account') }}" required>
                                        <option value="">{{ __('Select account') }}</option>
                                        @foreach($accounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->code_name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" inputmode="decimal"
                                        class="form-control debit-input" name="accounts[0][debit]">
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" inputmode="decimal"
                                        class="form-control credit-input" name="accounts[0][credit]">
                                </td>
                                <td>
                                    <input type="text" class="form-control" name="accounts[0][description]">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-row"
                                        aria-label="Remove">×</button>
                                </td>
                            </tr>
                            @endif
                        </tbody>

                        <tfoot>
                            <tr>
                                <th class="text-right" colspan="1">{{ __('Totals') }}</th>
                                <th id="total-debit">0.00</th>
                                <th id="total-credit">0.00</th>
                                <th colspan="2"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Row template --}}
                <template id="row-template">
                    <tr>
                        <td>
                            <select class="form-control account-select select2"
                                data-name="accounts[__INDEX__][account_id]"
                                data-placeholder="{{ __('Select account') }}" required>
                                <option value="">{{ __('Select account') }}</option>
                                @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->code_name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="number" step="0.01" min="0" inputmode="decimal"
                                class="form-control debit-input" data-name="accounts[__INDEX__][debit]">
                        </td>
                        <td>
                            <input type="number" step="0.01" min="0" inputmode="decimal"
                                class="form-control credit-input" data-name="accounts[__INDEX__][credit]">
                        </td>
                        <td>
                            <input type="text" class="form-control" data-name="accounts[__INDEX__][description]">
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-row"
                                aria-label="Remove">×</button>
                        </td>
                    </tr>
                </template>

                <div class="mt-3">
                    <button type="submit" class="btn btn-success">
                        {{ isset($entry) ? __('Update') : __('Save') }}
                    </button>
                    <a href="{{ route('seller.accounting.journals') }}" class="btn btn-light">
                        {{ __('Cancel') }}
                    </a>
                </div>

            </div>
        </div>
    </form>
</section>
@endsection

@push('page-script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tbody = document.getElementById('lines-body');
    const addBtn = document.getElementById('add-row');
    const tpl = document.getElementById('row-template');
    const totalD = document.getElementById('total-debit');
    const totalC = document.getElementById('total-credit');

    const qa = (el, s) => Array.from((el || document).querySelectorAll(s));
    const toNum = v => {
        const n = parseFloat(v);
        return isFinite(n) ? n : 0;
    };
    const fmt = n => (Math.round(n * 100) / 100).toFixed(2);

    // Init Select2 on account selects
    const initSelect2 = (root) => {
        if (!window.jQuery || !jQuery.fn.select2) return;
        (root ? qa(root, '.account-select') : qa(document, '.account-select')).forEach(el => {
            const $el = jQuery(el);
            if (!$el.hasClass('select2-hidden-accessible')) {
                $el.select2({
                    placeholder: el.dataset.placeholder || 'Select account',
                    allowClear: true,
                    width: '100%'
                });
            }
        });
    };

    const recalc = () => {
        const d = qa(tbody, '.debit-input').reduce((s, i) => s + toNum(i.value), 0);
        const c = qa(tbody, '.credit-input').reduce((s, i) => s + toNum(i.value), 0);
        totalD.textContent = fmt(d);
        totalC.textContent = fmt(c);
    };

    const syncRow = (row) => {
        const d = row.querySelector('.debit-input');
        const c = row.querySelector('.credit-input');
        if (!d || !c) return;
        const dv = toNum(d.value),
            cv = toNum(c.value);
        c.disabled = dv > 0;
        if (dv > 0) c.value = '';
        d.disabled = cv > 0;
        if (cv > 0) d.value = '';
    };

    const wireRow = (row) => {
        ['.debit-input', '.credit-input'].forEach(sel => {
            const i = row.querySelector(sel);
            if (i)['input', 'change'].forEach(e => i.addEventListener(e, () => {
                syncRow(row);
                recalc();
            }));
        });
        syncRow(row);
    };

    // Wire existing rows
    qa(tbody, 'tr').forEach(wireRow);
    initSelect2();
    recalc();

    // Add row
    let idx = Number(tbody.dataset.index || qa(tbody, 'tr').length || 1);
    const addRow = () => {
        const frag = tpl.content.cloneNode(true);
        frag.querySelectorAll('[data-name]').forEach(el => el.name = el.getAttribute('data-name').replace(
            '__INDEX__', idx));
        idx++;
        tbody.appendChild(frag);
        tbody.dataset.index = String(idx);
        const newRow = tbody.lastElementChild;
        initSelect2(newRow);
        wireRow(newRow);
        recalc();
    };
    if (addBtn) addBtn.addEventListener('click', e => {
        e.preventDefault();
        addRow();
    });

    // Remove row
    document.body.addEventListener('click', e => {
        if (e.target && e.target.classList.contains('remove-row')) {
            e.preventDefault();
            const tr = e.target.closest('tr');
            if (tr) tr.remove();
            recalc();
        }
    });

    // Balance check
    const form = tbody.closest('form');
    if (form) form.addEventListener('submit', e => {
        if (Math.abs(toNum(totalD.textContent) - toNum(totalC.textContent)) > 0.00001) {
            if (window.AIZ?.plugins?.notify) {
                AIZ.plugins.notify('danger', @json(__('Debit and Credit must be equal.')));
            } else {
                alert(@json(__('Debit and Credit must be equal.')));
            }
            e.preventDefault();
        }
    });
});
</script>
@endpush