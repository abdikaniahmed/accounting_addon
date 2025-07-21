@extends('admin.partials.master')

@section('title')
    {{ __('Edit Account Group') }}
@endsection

@section('main-content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.accounting.groups.update', $group->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Group Name</label>
                <input type="text" name="name" class="form-control" value="{{ $group->name }}" required>
            </div>
            <button class="btn btn-primary">Update</button>
        </form>
    </div>
</div>
@endsection
