@extends('admin.partials.master')

@section('title')
{{ __('Chart of Accounts') }}
@endsection

@section('accounting_active')
sidebar_active
@endsection

@section('main-content')
<div class="aiz-titlebar d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">{{ __('Chart of Accounts') }}</h5>
    @if(hasPermission('accounting_coa_create'))
    <div class="d-flex gap-2">
        <a href="{{ route('seller.accounting.coa.import.view') }}" class="btn btn-outline-primary mr-2">
            <i class="bx bx-upload"></i> {{ __('Import Accounts') }}
        </a>
        <a href="{{ route('seller.accounting.coa.create') }}" class="btn btn-primary">
            <i class="bi bi-plus"></i> {{ __('Add Account') }}
        </a>
    </div>
    @endif
</div>

@php
$grouped = $accounts->groupBy('type');
@endphp

@foreach($grouped as $type => $groupAccounts)
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0"><strong>{{ strtoupper($type) }}</strong></h6>
    </div>
    <div class="card-body p-0">
        <table class="table table-bordered table-striped mb-0">
            <thead>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Group') }}</th>
                    <th>{{ __('Code') }}</th>
                    <th>{{ __('Money') }}</th> {{-- NEW --}}
                    <th>{{ __('Status') }}</th>
                    @if(hasPermission('accounting_coa_update') || hasPermission('accounting_coa_delete'))
                    <th class="text-center">{{ __('Action') }}</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($groupAccounts->sortByDesc('is_money') as $account)
                <tr id="row_{{ $account->id }}">
                    <td>{{ $account->name }}</td>
                    <td>{{ $account->accountGroup?->name ?? '-' }}</td>
                    <td>{{ $account->code }}</td>
                    <td>
                        @if($account->is_money)
                        <span class="badge badge-success">{{ __('Yes') }}</span>
                        @else
                        <span class="badge badge-secondary">{{ __('No') }}</span>
                        @endif
                    </td>
                    <td>{{ $account->is_active ? 'Active' : 'Inactive' }}</td>
                    @if(hasPermission('accounting_coa_update') || hasPermission('accounting_coa_delete'))
                    <td class="text-center">
                        @if(hasPermission('accounting_coa_update'))
                        <a href="{{ route('seller.accounting.coa.edit', $account->id) }}"
                            class="btn btn-sm btn-outline btn-circle btn-primary" data-toggle="tooltip"
                            title="{{ __('Edit') }}">
                            <i class="bx bx-edit"></i>
                        </a>
                        @endif
                        @if(hasPermission('accounting_coa_delete'))
                        <a href="javascript:void(0);"
                            onclick="delete_row('accounting/chart-of-accounts/', {{ $account->id }})"
                            class="btn btn-sm btn-outline-danger btn-circle" data-toggle="tooltip"
                            title="{{ __('Delete') }}">
                            <i class="bx bx-trash"></i>
                        </a>
                        @endif
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endforeach
@endsection

@push('script')
@include('admin.common.delete-ajax')
@endpush