@extends('admin.partials.master')

@section('title') {{ __('Assets') }} @endsection
@section('accounting_active') sidebar_active @endsection
@section('assets') active @endsection

@section('main-content')
<div class="aiz-titlebar d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">{{ __('Fixed Assets') }}</h5>
    <a href="{{ route('admin.accounting.assets.create') }}" class="btn btn-primary">
        <i class="bx bx-plus"></i> {{ __('Record Asset') }}
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form class="mb-3">
            <div class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                        placeholder="{{ __('Search name or code') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-control">
                        <option value="">{{ __('All') }}</option>
                        <option value="active" {{ request('status')==='active'?'selected':'' }}>{{ __('Active') }}
                        </option>
                        <option value="inactive" {{ request('status')==='inactive'?'selected':'' }}>{{ __('Inactive') }}
                        </option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-outline-primary">{{ __('Filter') }}</button>
                    <a href="{{ route('admin.accounting.assets.index') }}" class="btn btn-light">{{ __('Reset') }}</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered aiz-table mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('Code') }}</th>
                        <th>{{ __('Asset Name') }}</th>
                        <th>{{ __('Asset Account') }}</th>
                        <th>{{ __('Purchase Date') }}</th>
                        <th class="text-right">{{ __('Cost') }}</th>
                        <th>{{ __('Depreciation') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-right">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assets as $a)
                    <tr id="row_{{ $a->id }}">
                        <td>{{ $a->id }}</td>
                        <td>{{ $a->asset_code ?? '-' }}</td>
                        <td>{{ $a->asset_name }}</td>
                        <td>{{ $a->assetAccount?->name ?? '-' }}</td>
                        <td>{{ \Illuminate\Support\Carbon::parse($a->purchase_date)->format('Y-m-d') }}</td>
                        <td class="text-right">{{ number_format($a->cost,2) }}</td>
                        <td>
                            @if($a->depreciation_method === 'straight_line')
                            <span class="badge badge-info">{{ __('Straight-line') }}</span>
                            @else
                            <span class="badge badge-secondary">{{ __('None') }}</span>
                            @endif
                        </td>
                        <td>
                            {!! $a->is_active
                            ? '<span class="badge badge-success">'.__('Active').'</span>'
                            : '<span class="badge badge-secondary">'.__('Inactive').'</span>' !!}
                        </td>
                        <td class="text-right">
                            <a href="{{ route('admin.accounting.assets.edit', $a->id) }}"
                                class="btn btn-sm btn-outline-primary" title="{{ __('Edit') }}">
                                <i class="bx bx-edit"></i>
                            </a>
                            <a href="javascript:void(0);" onclick="delete_row('accounting/assets/', {{ $a->id }})"
                                class="btn btn-sm btn-outline-danger" title="{{ __('Delete') }}">
                                <i class="bx bx-trash"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted p-4">{{ __('No assets found.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $assets->links() }}
        </div>
    </div>
</div>
@endsection

@push('script')
@include('admin.common.delete-ajax')
@endpush