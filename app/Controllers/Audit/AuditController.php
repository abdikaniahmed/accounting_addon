<?php

namespace App\Http\Controllers\Admin\Addons;
//new Addon
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use OwenIt\Auditing\Models\Audit;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = Audit::with('user')->latest();

        // Filters
        if ($request->filled('event')) {
            $query->where('event', $request->string('event'));
        }

        if ($request->filled('model')) {
            // Accepts short ("Product") or FQCN; we match on class basename
            $model = $request->string('model');
            $query->where(function ($q) use ($model) {
                $q->where('auditable_type', 'LIKE', "%\\{$model}")
                  ->orWhere('auditable_type', $model);
            });
        }

        if ($request->filled('user')) {
            $user = $request->string('user');
            $query->whereHas('user', function ($uq) use ($user) {
                $uq->where('name', 'like', "%{$user}%")->orWhere('email', 'like', "%{$user}%");
            });
        }

        if ($request->filled('from')) {
            $query->where('created_at', '>=', $request->date('from')->startOfDay());
        }
        if ($request->filled('to')) {
            $query->where('created_at', '<=', $request->date('to')->endOfDay());
        }

        // Pagination size (default 25)
        $perPage = (int) $request->query('per_page', 25);
        $perPage = in_array($perPage, [10,25,50,100]) ? $perPage : 25;

        $audits = $query->paginate($perPage)->appends($request->query());

        // For the filter dropdown
        $events = ['created', 'updated', 'deleted', 'restored'];

        return view('admin.audit.index', compact('audits', 'events'));
    }

    public function show(Audit $audit)
    {
        // Optional: guard / policy check here …
        return view('admin.audit.show', compact('audit'));
    }
}