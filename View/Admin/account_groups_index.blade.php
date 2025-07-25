@extends('admin.partials.master')

@section('title')
    {{ __('Account Groups') }}
@endsection

@section('accounting_active')
    sidebar_active
@endsection

@section('main-content')
    <div class="aiz-titlebar d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">{{ __('Account Groups') }}</h5>
        <div>
            <a href="{{ route('admin.accounting.groups.import.view') }}" class="btn btn-outline-primary mr-2">
                <i class="bx bx-upload"></i> {{ __('Import Groups') }}
            </a>
            <a href="{{ route('admin.accounting.groups.create') }}" class="btn btn-primary">
                <i class="bx bx-plus"></i> {{ __('Add Group') }}
            </a>
        </div>
    </div>


    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped table-hover" id="group-table">
                <thead>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th class="text-center" width="15%">{{ __('Action') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($groups as $group)
                    <tr id="row_{{ $group->id }}">
                        <td class="align-middle">{{ $group->name }}</td>
                        <td class="text-center">
                            <a href="{{ route('admin.accounting.groups.edit', $group->id) }}"
                               class="btn btn-outline-secondary btn-circle" data-toggle="tooltip"
                               title="{{ __('Edit') }}">
                                <i class="bx bx-edit"></i>
                            </a>
                            <a href="javascript:void(0)"
                               onclick="delete_row('accounting/groups/', {{ $group->id }})"
                               class="btn btn-outline-danger btn-circle" data-toggle="tooltip"
                               title="{{ __('Delete') }}">
                                <i class="bx bx-trash"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@include('admin.common.delete-ajax')

@push('style')
    <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">@endpush
@push('script')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#group-table').DataTable({
                responsive: true,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "{{ __('Search...') }}"
                }
            });
        });
    </script>
@endpush
