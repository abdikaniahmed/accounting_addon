@extends('admin.partials.master')

@section('title')
    {{ __('Create Account Group') }}
@endsection

@section('main-content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.accounting.groups.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Group Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <button class="btn btn-primary">Save Group</button>
        </form>
    </div>
</div>
@endsection