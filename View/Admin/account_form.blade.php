@extends('admin.partials.master')

@section('title')
    {{ isset($account) ? __('Edit Account') : __('Add Account') }}
@endsection

@section('accounting_active')
    sidebar_active
@endsection

@section('main-content')
<div class="aiz-titlebar">
    <h5 class="mb-0">{{ isset($account) ? 'Edit Account' : 'Add New Account' }}</h5>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ isset($account) 
            ? route('admin.accounting.coa.update', $account->id) 
            : route('admin.accounting.coa.store') 
        }}" method="POST">
            @csrf
            @if(isset($account))
                @method('PUT')
            @endif

            <div class="form-group">
                <label>Account Name</label>
                <input type="text" name="name" class="form-control" 
                       value="{{ old('name', $account->name ?? '') }}" required>
            </div>

            <div class="form-group">
                <label>Account Type</label>
                <select name="type" class="form-control" required>
                    @foreach(['asset', 'liability', 'equity', 'revenue', 'expense'] as $type)
                        <option value="{{ $type }}" 
                            {{ (old('type', $account->type ?? '') === $type) ? 'selected' : '' }}>
                            {{ ucfirst($type) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>{{ __('Account Group') }}</label>
                <select name="account_group_id" class="form-control">
                    <option value="">{{ __('Select Group (Optional)') }}</option>
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}" 
                            {{ (old('account_group_id', $account->account_group_id ?? '') == $group->id) ? 'selected' : '' }}>
                            {{ $group->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Account Code (optional)</label>
                <input type="text" name="code" class="form-control"
                       value="{{ old('code', $account->code ?? '') }}">
            </div>

            <div class="form-check mb-3">
                <input type="checkbox" name="is_active" class="form-check-input" value="1"
                    {{ old('is_active', $account->is_active ?? true) ? 'checked' : '' }}>
                <label class="form-check-label">Active</label>
            </div>

            <button type="submit" class="btn btn-primary">
                {{ isset($account) ? __('Update Account') : __('Save Account') }}
            </button>
            <a href="{{ route('admin.accounting.coa') }}" class="btn btn-secondary">
                {{ __('Cancel') }}
            </a>
        </form>
    </div>
</div>
@endsection
