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
        </div>

        <div class="card">
            <div class="card-body">
                <table class="table aiz-table">
                    <thead>
                        <tr>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Description') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($entries as $entry)
                            <tr>
                                <td>{{ $entry->date }}</td>
                                <td>{{ $entry->description }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection
