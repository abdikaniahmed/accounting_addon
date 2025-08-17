@extends('admin.partials.master')

@section('title') {{ isset($asset->id) ? __('Edit Asset') : __('Record Asset') }} @endsection
@section('accounting_active') sidebar_active @endsection
@section('assets') active @endsection

@section('main-content')
<div class="aiz-titlebar d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">{{ isset($asset->id) ? __('Edit Asset') : __('Record Asset') }}</h5>
    <a href="{{ route('admin.accounting.assets.index') }}" class="btn btn-secondary">
        <i class="las la-arrow-left"></i> {{ __('Back to List') }}
    </a>
</div>

<form
    action="{{ isset($asset->id) ? route('admin.accounting.assets.update',$asset->id) : route('admin.accounting.assets.store') }}"
    method="POST">
    @csrf
    @if(isset($asset->id)) @method('PUT') @endif

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="form-group col-md-4">
                    <label>{{ __('Asset Name') }} <span class="text-danger">*</span></label>
                    <input type="text" name="asset_name" class="form-control"
                        value="{{ old('asset_name', $asset->asset_name) }}" required>
                </div>

                <div class="form-group col-md-3">
                    <label>{{ __('Asset Code / Tag') }}</label>
                    <input type="text" name="asset_code" class="form-control"
                        value="{{ old('asset_code', $asset->asset_code) }}" placeholder="ASSET-001">
                </div>

                <div class="form-group col-md-3">
                    <label>{{ __('Purchase Date') }} <span class="text-danger">*</span></label>
                    <input type="date" name="purchase_date" class="form-control"
                        value="{{ old('purchase_date', isset($asset->purchase_date)?\Illuminate\Support\Carbon::parse($asset->purchase_date)->format('Y-m-d'):now()->format('Y-m-d')) }}"
                        required>
                </div>

                <div class="form-group col-md-2">
                    <label>{{ __('Cost') }} <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="cost" class="form-control"
                        value="{{ old('cost', $asset->cost) }}" required>
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-6">
                    <label>{{ __('Asset Account') }} <span class="text-danger">*</span></label>
                    <select name="asset_account_id" class="form-control select2" required>
                        <option value="">{{ __('Select account') }}</option>
                        @foreach($assetAccounts as $id => $name)
                        <option value="{{ $id }}"
                            {{ (int)old('asset_account_id', $asset->asset_account_id) === (int)$id ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                        @endforeach
                    </select>
                    <small class="text-muted">{{ __('Pick a Balance Sheet asset account (e.g., Equipment).') }}</small>
                </div>

                <div class="form-group col-md-6">
                    <label>{{ __('Payment Account (Cash/Bank)') }} <span class="text-muted">—
                            {{ __('optional') }}</span></label>
                    <select name="payment_account_id" class="form-control select2">
                        <option value="">{{ __('Not paid / post later') }}</option>
                        @foreach($moneyAccounts as $id => $name)
                        <option value="{{ $id }}"
                            {{ (int)old('payment_account_id', $asset->payment_account_id) === (int)$id ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                        @endforeach
                    </select>
                    <small
                        class="text-muted">{{ __('If selected, system will post Dr Asset / Cr Cash-Bank for the cost.') }}</small>
                </div>
            </div>

            <hr>
            <h6 class="mb-2">{{ __('Depreciation (optional)') }}</h6>

            <div class="row">
                <div class="form-group col-md-4">
                    <label>{{ __('Method') }}</label>
                    <select name="depreciation_method" class="form-control">
                        @php $method = old('depreciation_method', $asset->depreciation_method ?? 'none'); @endphp
                        <option value="none" {{ $method==='none'?'selected':'' }}>{{ __('None (don’t depreciate)') }}
                        </option>
                        <option value="straight_line" {{ $method==='straight_line'?'selected':'' }}>
                            {{ __('Straight-line') }}</option>
                    </select>
                </div>

                <div class="form-group col-md-4">
                    <label>{{ __('Useful Life (months)') }}</label>
                    <input type="number" name="useful_life_months" min="1" class="form-control"
                        value="{{ old('useful_life_months', $asset->useful_life_months) }}" placeholder="e.g. 60">
                </div>

                <div class="form-group col-md-4">
                    <label>{{ __('Salvage Value') }}</label>
                    <input type="number" step="0.01" min="0" name="salvage_value" class="form-control"
                        value="{{ old('salvage_value', $asset->salvage_value) }}" placeholder="0.00">
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-3">
                    <label>{{ __('Status') }}</label>
                    <select name="is_active" class="form-control">
                        @php $active = (bool) old('is_active', $asset->is_active ?? true); @endphp
                        <option value="1" {{ $active ? 'selected' : '' }}>{{ __('Active') }}</option>
                        <option value="0" {{ !$active ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                    </select>
                </div>
            </div>

            <div class="mt-3">
                <button class="btn btn-primary">{{ isset($asset->id) ? __('Update Asset') : __('Save Asset') }}</button>
                <a href="{{ route('admin.accounting.assets.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
            </div>
        </div>
    </div>
</form>
@endsection

@push('script')
<script>
if (window.$) $('.select2').select2({
    width: '100%'
});
</script>
@endpush