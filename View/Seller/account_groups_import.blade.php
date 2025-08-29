{{-- resources/views/addons/accountingSeller/account_groups_import.blade.php --}}
@extends('admin.partials.master')

@section('title') {{ __('Import Account Groups') }} @endsection
@section('accounting_active') sidebar_active @endsection

@section('main-content')
<div class="aiz-titlebar mb-3">
    <h5 class="mb-0">{{ __('Import Account Groups') }}</h5>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('seller.accounting.groups.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="file">{{ __('Upload File') }} <span class="text-danger">*</span></label>
                <input type="file" class="form-control" name="file" id="file" accept=".csv,.xlsx,.xls" required>
                <small class="text-muted">
                    {{ __('Accepted formats: CSV, XLSX, XLS. Each row should contain a valid group name.') }}
                </small>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="bx bx-import"></i> {{ __('Import') }}
            </button>

            <a href="{{ route('seller.accounting.groups.sample.download') }}" class="btn btn-link">
                <i class="bx bx-download"></i> {{ __('Download Sample Excel') }}
            </a>

            <a href="{{ route('seller.accounting.groups.index') }}" class="btn btn-outline-secondary ml-2">
                <i class="bx bx-arrow-back"></i> {{ __('Cancel') }}
            </a>
        </form>
    </div>
</div>
@endsection