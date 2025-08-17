{{-- resources/views/addons/accounting/assets_index.blade.php --}}
@extends('admin.partials.master')

@section('title') {{ __('Assets') }} @endsection
@section('accounting_active') sidebar_active @endsection
@section('assets') active @endsection

@section('main-content')
<div class="aiz-titlebar d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">{{ __('Fixed Assets') }}</h5>

    <div class="d-flex align-items-center gap-2">
        {{-- Bulk: depreciation for ALL eligible assets for a month --}}
        <form action="{{ route('admin.accounting.assets.depr.run') }}" method="POST" class="form-inline mr-2 d-flex">
            @csrf
            <input type="month" name="period" value="{{ now()->subMonthNoOverflow()->format('Y-m') }}"
                class="form-control mr-2" style="min-width: 150px">
            <button class="btn btn-outline-primary">
                <i class="las la-calculator"></i> {{ __('Post Depreciation') }}
            </button>
        </form>

        <a href="{{ route('admin.accounting.assets.create') }}" class="btn btn-primary">
            <i class="bx bx-plus"></i> {{ __('Record Asset') }}
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        {{-- Filters --}}
        <form class="mb-3">
            <div class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                        placeholder="{{ __('Search name or code') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-control">
                        <option value="">{{ __('All') }}</option>
                        <option value="active" {{ request('status')==='active'   ? 'selected' : '' }}>{{ __('Active') }}
                        </option>
                        <option value="inactive" {{ request('status')==='inactive' ? 'selected' : '' }}>
                            {{ __('Inactive') }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-outline-primary">{{ __('Filter') }}</button>
                    <a href="{{ route('admin.accounting.assets.index') }}" class="btn btn-light">{{ __('Reset') }}</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover aiz-table mb-0 text-sm">
                <thead class="thead-light text-xs">
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
                <tbody class="align-middle">
                    @forelse($assets as $a)
                    <tr id="row_{{ $a->id }}">
                        <td>{{ $a->id }}</td>
                        <td>{{ $a->asset_code ?? '-' }}</td>
                        <td>{{ $a->asset_name }}</td>
                        <td>{{ $a->assetAccount?->name ?? '-' }}</td>
                        <td>{{ \Illuminate\Support\Carbon::parse($a->purchase_date)->format('Y-m-d') }}</td>
                        <td class="text-right">{{ number_format($a->cost, 2) }}</td>
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
                            <div class="d-flex align-items-center justify-content-end gap-1">

                                {{-- Month Selector + Post Depreciation --}}
                                <form action="{{ route('admin.accounting.assets.depr.asset', $a->id) }}" method="POST"
                                    class="d-flex align-items-center gap-1">
                                    @csrf
                                    <input type="month" name="period"
                                        value="{{ request()->old('period', now()->subMonthNoOverflow()->format('Y-m')) }}"
                                        class="form-control form-control-sm text-xs"
                                        style="width: 110px; font-size: 0.8rem;">
                                    <button class="btn btn-xs btn-outline-secondary"
                                        title="{{ __('Post Depreciation for this asset') }}">
                                        <i class="bx bx-calculator" style="font-size:14px;"></i>
                                    </button>
                                </form>

                                {{-- Edit --}}
                                <a href="{{ route('admin.accounting.assets.edit', $a->id) }}"
                                    class="btn btn-xs btn-outline-primary" title="{{ __('Edit') }}">
                                    <i class="bx bx-edit" style="font-size:14px;"></i>
                                </a>

                                {{-- Delete --}}
                                <a href="javascript:void(0);" onclick="delete_row('accounting/assets/', {{ $a->id }})"
                                    class="btn btn-xs btn-outline-danger" title="{{ __('Delete') }}">
                                    <i class="bx bx-trash" style="font-size:14px;"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted p-3 text-sm">{{ __('No assets found.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

        </div>

        <div class="mt-3">
            {{ $assets->links() }}
        </div>

        <div class="mt-2 text-muted small">
            {{ __('Tip: The month selector beside each row lets you post depreciation for just that asset and month.') }}
        </div>
    </div>
</div>
@endsection

@push('script')
@include('admin.common.delete-ajax')
@endpush