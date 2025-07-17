@extends('admin.partials.master')

@section('title')
    {{ __('Chart of Accounts') }}
@endsection

@section('accounting_active')
    sidebar_active
@endsection

@section('main-content')
<div class="aiz-titlebar">
    <h5 class="mb-0">Chart of Accounts</h5>
</div>

<div class="card">
    <div class="card-header">
        <a href="{{ route('admin.accounting.coa.create') }}" class="btn btn-primary">+ Add Account</a>
    </div>
    <div class="card-body">
        <table class="table aiz-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Code</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($accounts as $account)
                <tr>
                    <td>{{ $account->name }}</td>
                    <td>{{ ucfirst($account->type) }}</td>
                    <td>{{ $account->code }}</td>
                    <td>{{ $account->is_active ? 'Active' : 'Inactive' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
