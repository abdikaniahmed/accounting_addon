@extends('admin.partials.master')

@section('title')
    {{ __('Journal Detail') }}
@endsection

@section('main-content')
<section class="section">
    <div class="section-header">
        <h1>{{ __('Journal Entry') }}</h1>
    </div>

    <div class="card">
        <div class="card-body">
            <p><strong>{{ __('Journal Number') }}:</strong> {{ $entry->journal_number }}</p>
            <p><strong>{{ __('Date') }}:</strong> {{ $entry->date }}</p>
            <p><strong>{{ __('Reference') }}:</strong> {{ $entry->reference }}</p>
            <p><strong>{{ __('Description') }}:</strong> {{ $entry->description }}</p>

            <table class="table table-bordered mt-4">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('Account') }}</th>
                        <th>{{ __('Note') }}</th>
                        <th>{{ __('Debit') }}</th>
                        <th>{{ __('Credit') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($entry->items as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $item->account->code }} - {{ $item->account->name }}</td>
                            <td>{{ $item->description ?? '-' }}</td>
                            <td>{{ $item->type === 'debit' ? number_format($item->amount, 2) : '' }}</td>
                            <td>{{ $item->type === 'credit' ? number_format($item->amount, 2) : '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3">{{ __('Total') }}</th>
                        <th>{{ number_format($entry->items->where('type', 'debit')->sum('amount'), 2) }}</th>
                        <th>{{ number_format($entry->items->where('type', 'credit')->sum('amount'), 2) }}</th>
                    </tr>
                </tfoot>
            </table>

            <a href="{{ route('admin.accounting.journals') }}" class="btn btn-light mt-3">{{ __('Back') }}</a>
        </div>
    </div>
</section>
@endsection
