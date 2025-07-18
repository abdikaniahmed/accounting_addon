@extends('admin.partials.master')

@section('title')
    {{ __('Add Account') }}
@endsection

@section('accounting_active')
    sidebar_active
@endsection

@section('main-content')
<div class="aiz-titlebar">
    <h5 class="mb-0">Add New Account</h5>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.accounting.coa.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Account Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Account Type</label>
                <select name="type" class="form-control" required>
                    <option value="asset">Asset</option>
                    <option value="liability">Liability</option>
                    <option value="equity">Equity</option>
                    <option value="revenue">Revenue</option>
                    <option value="expense">Expense</option>
                </select>
            </div>

            <div class="form-group">
                <label>Account Code (optional)</label>
                <input type="text" name="code" class="form-control">
            </div>

            <div class="form-check">
                <input type="checkbox" name="is_active" class="form-check-input" checked value="1">
                <label class="form-check-label">Active</label>
            </div>

            <button type="submit" class="btn btn-primary mt-3">Save Account</button>
        </form>
    </div>
</div>
@endsection
