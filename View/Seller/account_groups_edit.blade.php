{{-- resources/views/addons/accountingSeller/account_groups_edit.blade.php --}}
@extends('admin.partials.master')

@section('title') {{ __('Edit Account Group') }} @endsection

@section('main-content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('seller.accounting.groups.update', $group->id) }}" method="POST">
            @csrf @method('PUT')
            <div class="form-group">
                <label>{{ __('Group Name') }}</label>
                <input type="text" name="name" class="form-control" value="{{ $group->name }}" required>
            </div>
            <button class="btn btn-primary">{{ __('Update') }}</button>
            <a href="{{ route('seller.accounting.groups.index') }}"
                class="btn btn-outline-secondary ml-2">{{ __('Cancel') }}</a>
        </form>
    </div>
</div>
@endsection