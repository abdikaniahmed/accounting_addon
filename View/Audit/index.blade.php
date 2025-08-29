@extends('admin.partials.master')
@section('title') {{ __('Audit Logs') }} @endsection

@section('main-content')
<div class="aiz-titlebar d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">{{ __('Audit Logs') }}</h5>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-2">
                <label class="form-label">{{ __('Event') }}</label>
                <select name="event" class="form-control aiz-selectpicker" data-live-search="true">
                    <option value="">{{ __('All') }}</option>
                    @foreach($events as $ev)
                    <option value="{{ $ev }}" @selected(request('event')===$ev)>{{ ucfirst($ev) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">{{ __('Model') }}</label>
                <input type="text" name="model" value="{{ request('model') }}" class="form-control"
                    placeholder="e.g. Account, AccountGroup">
            </div>

            <div class="col-md-2">
                <label class="form-label">{{ __('User') }}</label>
                <input type="text" name="user" value="{{ request('user') }}" class="form-control"
                    placeholder="name / email / username">
            </div>

            <div class="col-md-2">
                <label class="form-label">{{ __('From') }}</label>
                <input type="date" name="from" value="{{ request('from') }}" class="form-control">
            </div>

            <div class="col-md-2">
                <label class="form-label">{{ __('To') }}</label>
                <input type="date" name="to" value="{{ request('to') }}" class="form-control">
            </div>

            <div class="col-md-2">
                <label class="form-label">{{ __('Per Page') }}</label>
                <select name="per_page" class="form-control aiz-selectpicker">
                    @foreach([10,25,50,100] as $pp)
                    <option value="{{ $pp }}" @selected(request('per_page',25)==$pp)>{{ $pp }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 d-flex gap-2 mt-2">
                <button class="btn btn-primary">
                    <i class="las la-search"></i> {{ __('Filter') }}
                </button>
                <a href="{{ route('admin.audits.index') }}" class="btn btn-outline-secondary">
                    {{ __('Reset') }}
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body table-responsive">
        <table class="table aiz-table">
            <thead>
                <tr>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('User') }}</th>
                    <th>{{ __('Guard') }}</th>
                    <th>{{ __('Event') }}</th>
                    <th>{{ __('Model') }}</th>
                    <th>{{ __('ID') }}</th>
                    <th>{{ __('URL / IP') }}</th>
                    <th class="text-right">{{ __('Action') }}</th>
                </tr>
            </thead>

            <tbody>
                @forelse($audits as $a)
                @php

                // Friendly display for Yoori/Sentinel user models

                $u = $a->user;
                $displayName = 'System';
                if ($u) {
                $full = trim(($u->first_name ?? '').' '.($u->last_name ?? ''));
                $displayName = $full !== '' ? $full : ($u->email ?? 'System');
                }


                // tags column is varchar(255) in DB; may contain JSON or plain/null
                $tagsRaw = $a->tags; // varchar(255)
                $tagsArray = is_array($tagsRaw) ? $tagsRaw : (json_decode($tagsRaw ?? '', true) ?: []);
                $guardFromArray = data_get($tagsArray, '_audit_guard');
                $guardFromTag = null;
                if (is_array($tagsArray)) {
                foreach ($tagsArray as $tag) {
                if (is_string($tag) && str_starts_with($tag, 'guard:')) {
                $guardFromTag = substr($tag, 6);
                break;
                }
                }
                }
                $guard = $guardFromArray ?: $guardFromTag;
                @endphp

                <tr>
                    <td>{{ optional($a->created_at)->format('Y-m-d H:i') }}</td>

                    <td>
                        {{ $displayName }}
                        @if($u && $u->email)
                        <div class="small text-muted">{{ $u->email }}</div>
                        @endif
                    </td>

                    <td>
                        @if($guard)
                        <span class="badge badge-secondary">{{ ucfirst($guard) }}</span>
                        @else
                        <span class="text-muted">—</span>
                        @endif
                    </td>

                    <td><span class="badge badge-info text-uppercase">{{ $a->event }}</span></td>

                    <td>{{ class_basename($a->auditable_type) }}</td>
                    <td>#{{ $a->auditable_id }}</td>

                    <td>
                        <div class="small text-muted">
                            {{ \Illuminate\Support\Str::limit($a->url, 40) }}<br>
                            {{ $a->ip_address ?: '—' }}
                        </div>
                    </td>

                    <td class="text-right">
                        <a href="{{ route('admin.audits.show', $a->id) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="las la-eye"></i> {{ __('View') }}
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">{{ __('No audit logs found.') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-3">
            {{ $audits->links() }}
        </div>
    </div>
</div>
@endsection