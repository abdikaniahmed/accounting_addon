@extends('admin.partials.master')

@section('title')
    {{ __('Journal Entries') }}
@endsection

@section('journal_entries')
    sidebar_active
@endsection

@section('main-content')
<section class="section">
    <div class="section-header">
        <h1>{{ __('Journal Entries') }}</h1>
        <a href="{{ route('admin.accounting.journals.create') }}" class="btn btn-primary ml-auto">{{ __('Add New') }}</a>
    </div>

    <div class="card">
        <div class="card-body">
            <table class="table aiz-table">
                <thead>
                    <tr>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Description') }}</th>
                        <th>{{ __('Total') }}</th>
                        <th>{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($entries as $entry)
                        <tr>
                            <td>{{ $entry->date }}</td>
                            <td>{{ $entry->description }}</td>
                            <td>
                                {{ number_format($entry->items->where('type', 'debit')->sum('amount'), 2) }}
                            </td>
                            <td>
                                <a href="{{ route('admin.accounting.journals.show', $entry->id) }}" class="btn btn-sm btn-info">
                                    {{ __('Show') }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
