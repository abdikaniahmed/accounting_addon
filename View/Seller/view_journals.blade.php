@extends('admin.partials.master')

@section('title')
{{ __('Journal Entries') }}
@endsection

@section('journal_entries')
sidebar_active
@endsection

@push('style')
<link href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" rel="stylesheet">
<style>
#journal-table tbody tr {
    cursor: pointer;
}

#journal-table td .btn {
    cursor: default !important;
}
</style>
@endpush

@section('main-content')
<section class="section">
    <div class="section-header">
        <h1>{{ __('Journal Entries') }}</h1>
        <a href="{{ route('admin.accounting.journals.create') }}" class="btn btn-primary ml-auto">
            {{ __('Add New') }}
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table aiz-table" id="journal-table">
                    <thead>
                        <tr>
                            <th>{{ __('Journal No') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Description') }}</th>
                            <th>{{ __('Total') }}</th>
                            <th>{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($entries as $entry)
                        <tr data-href="{{ route('admin.accounting.journals.show', $entry->id) }}">
                            <td>
                                <span class="badge badge-success">{{ $entry->journal_number }}</span>
                            </td>
                            <td>{{ $entry->date }}</td>
                            <td>{{ $entry->description }}</td>
                            <td>{{ number_format($entry->journalItems->where('type','debit')->sum('amount'), 2) }}</td>
                            <td>
                                <a href="{{ route('admin.accounting.journals.edit', $entry->id) }}"
                                    class="btn btn-sm btn-primary" data-toggle="tooltip" title="{{ __('Edit') }}">
                                    <i class="bx bx-edit"></i>
                                </a>
                                <a href="javascript:void(0);"
                                    onclick="delete_row('accounting/journals/', {{ $entry->id }})"
                                    class="btn btn-sm btn-danger" data-toggle="tooltip" title="{{ __('Delete') }}">
                                    <i class="bx bx-trash"></i>
                                </a>

                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection

@include('admin.common.delete-ajax')

@push('script')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    $('#journal-table').DataTable({
        pageLength: 10,
        ordering: true,
        language: {
            search: "{{ __('Search') }}:",
            lengthMenu: "{{ __('Show _MENU_ entries') }}",
            info: "{{ __('Showing _START_ to _END_ of _TOTAL_ entries') }}",
            paginate: {
                next: "{{ __('Next') }}",
                previous: "{{ __('Previous') }}"
            }
        },
        columnDefs: [{
            orderable: false,
            targets: 4
        }]
    });

    // Tooltips for action buttons
    $('[data-toggle="tooltip"]').tooltip();

    // Make table rows clickable (excluding buttons)
    $('#journal-table tbody').on('click', 'tr', function(e) {
        if (!$(e.target).closest('td').find('.btn').length) {
            const href = $(this).data('href');
            if (href) window.location = href;
        }
    });
});
</script>
@endpush