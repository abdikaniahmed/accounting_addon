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
    <a href="{{ route('admin.accounting.groups.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> {{ __('Add Group') }}
    </a>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-bordered table-striped table-hover" id="group-table">
            <thead>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th class="text-center" width="10%">{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($groups as $group)
                    <tr>
                        <td>{{ $group->name }}</td>
                        <td class="text-center">
                            <a href="{{ route('admin.accounting.groups.edit', $group->id) }}"
                               class="btn btn-sm btn-circle btn-outline-info" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>

                            <form action="{{ route('admin.accounting.groups.destroy', $group->id) }}"
                                  method="POST" class="d-inline-block"
                                  onsubmit="return confirm('Delete this group?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-circle btn-outline-danger" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('script')
    <!-- Include DataTables if not already included -->
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

@push('style')
    <!-- Include DataTables CSS if not already included -->
    <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">
@endpush
