@extends('admin.partials.master')

@section('title')
    {{ __('Import Chart of Accounts') }}
@endsection

@section('accounting_active')
    sidebar_active
@endsection

@section('main-content')
<div class="aiz-titlebar mb-3">
    <h5 class="mb-0">{{ __('Import Chart of Accounts') }}</h5>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.accounting.coa.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="file">{{ __('Upload File') }} <span class="text-danger">*</span></label>
                <input type="file" class="form-control" name="file" id="file" accept=".csv,.xlsx,.xls" required>
                <small class="text-muted">
                    {{ __('Columns: Name, Type, Code, Group, Active (Yes/No). Supported formats: CSV, XLSX, XLS.') }}
                </small>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="bx bx-import"></i> {{ __('Import') }}
            </button>

            <a href="{{ route('admin.accounting.coa.sample.download') }}" class="btn btn-link">
                <i class="bx bx-download"></i> {{ __('Download Sample Excel') }}
            </a>

            <a href="{{ route('admin.accounting.coa') }}" class="btn btn-outline-secondary ml-2">
                <i class="bx bx-arrow-back"></i> {{ __('Cancel') }}
            </a>
        </form>
    </div>
</div>
@endsection
