@extends('admin.partials.master')

@section('title') {{ __('Audit Detail') }} @endsection

@section('main-content')
<div class="aiz-titlebar d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">{{ __('Audit Detail') }}</h5>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.audits.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="las la-arrow-left"></i> {{ __('Back') }}
        </a>
    </div>
</div>

@php
// ---- Helper values for this view ----
$createdAt = optional($audit->created_at)->format('Y-m-d H:i:s');

// Guard can be stored either as: ['_audit_guard' => 'web'] OR ['guard:web', 'Account']
$tagsArray = (array) ($audit->tags ?? []);
$guard = null;

// 1) associative style
if (is_array($tagsArray) && array_key_exists('_audit_guard', $tagsArray)) {
$guard = $tagsArray['_audit_guard'];
}

// 2) string tag style: find first "guard:*"
if (!$guard && !array_key_exists('_audit_guard', $tagsArray)) {
foreach ($tagsArray as $tag) {
if (is_string($tag) && str_starts_with($tag, 'guard:')) {
$guard = substr($tag, 6); // after "guard:"
break;
}
}
}

$guardLabel = $guard ? ucfirst($guard) : null;

$old = $audit->old_values ?? [];
$new = $audit->new_values ?? [];
@endphp

<div class="row">
    {{-- General --}}
    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>{{ __('General') }}</strong>
                <span class="small text-muted">#{{ $audit->id }}</span>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">{{ __('Date') }}</dt>
                    <dd class="col-sm-8">{{ $createdAt ?? '—' }}</dd>

                    <dt class="col-sm-4">{{ __('User') }}</dt>
                    <dd class="col-sm-8">
                        @php
                        $u = $audit->user;
                        $full = $u ? trim(($u->first_name ?? '').' '.($u->last_name ?? '')) : '';
                        $displayName = $full !== '' ? $full : ($u->name ?? null);
                        @endphp

                        {{ $displayName ?? 'System' }}

                        @if($u && $u->email)
                        <div class="text-muted small">{{ $u->email }}</div>
                        @endif
                    </dd>

                    <dt class="col-sm-4">{{ __('Guard') }}</dt>
                    <dd class="col-sm-8">
                        @if($guardLabel)
                        <span class="badge badge-secondary">{{ $guardLabel }}</span>
                        @else
                        <span class="text-muted">—</span>
                        @endif
                    </dd>

                    <dt class="col-sm-4">{{ __('Event') }}</dt>
                    <dd class="col-sm-8">
                        <span class="badge badge-info text-uppercase">{{ $audit->event }}</span>
                    </dd>

                    <dt class="col-sm-4">{{ __('Model') }}</dt>
                    <dd class="col-sm-8">
                        {{ class_basename($audit->auditable_type) }}
                        @if($audit->auditable_id) #{{ $audit->auditable_id }} @endif
                    </dd>

                    <dt class="col-sm-4">{{ __('URL') }}</dt>
                    <dd class="col-sm-8">
                        @if($audit->url)
                        <a href="{{ $audit->url }}" target="_blank" rel="noopener noreferrer">
                            {{ \Illuminate\Support\Str::limit($audit->url, 70) }}
                        </a>
                        @else
                        <span class="text-muted">—</span>
                        @endif
                    </dd>

                    <dt class="col-sm-4">{{ __('IP Address') }}</dt>
                    <dd class="col-sm-8">{{ $audit->ip_address ?? '—' }}</dd>

                    <dt class="col-sm-4">{{ __('User Agent') }}</dt>
                    <dd class="col-sm-8">
                        <div class="small text-muted" style="word-break: break-word;">
                            {{ $audit->user_agent ?? '—' }}
                        </div>
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    {{-- Tags / Metadata --}}
    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header"><strong>{{ __('Tags / Metadata') }}</strong></div>
            <div class="card-body">
                @if(!empty($tagsArray))
                <pre class="mb-0"
                    style="white-space: pre-wrap">@json($tagsArray, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)</pre>
                @else
                <span class="text-muted">—</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Old Values --}}
    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>{{ __('Old Values') }}</strong>
                <a class="small text-muted" href="{{ url()->previous() }}"
                    onclick="window.history.back(); return false;">
                    {{ __('Back to list') }}
                </a>
            </div>
            <div class="card-body">
                @if(!empty($old))
                <pre class="mb-0"
                    style="white-space: pre-wrap">@json($old, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)</pre>
                @else
                <span class="text-muted">—</span>
                @endif
            </div>
        </div>
    </div>

    {{-- New Values --}}
    <div class="col-lg-6">
        <div class="card mb-3">
            <div class="card-header"><strong>{{ __('New Values') }}</strong></div>
            <div class="card-body">
                @if(!empty($new))
                <pre class="mb-0"
                    style="white-space: pre-wrap">@json($new, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)</pre>
                @else
                <span class="text-muted">—</span>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection