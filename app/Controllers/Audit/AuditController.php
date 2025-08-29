<?php

namespace App\Http\Controllers\Admin\Addons;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OwenIt\Auditing\Models\Audit;
use App\Models\User as AppUser; // <- important for morph type checks

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = Audit::with('user')->latest();

        // Event
        if ($request->filled('event')) {
            $query->where('event', (string) $request->input('event'));
        }

        // Model (accepts short name or FQCN)
        if ($request->filled('model')) {
            $model = trim((string) $request->input('model'));
            $query->where(function ($q) use ($model) {
                $q->where('auditable_type', 'LIKE', "%\\{$model}")
                  ->orWhere('auditable_type', $model);
            });
        }

        // User (search email, username, first+last)

// ...

if ($request->filled('user')) {
    $term = trim((string) $request->input('user'));

    $query->whereHas('user', function ($uq) use ($term) {
        $uq->where('email', 'like', "%{$term}%")
           ->orWhere('first_name', 'like', "%{$term}%")
           ->orWhere('last_name', 'like', "%{$term}%")
           ->orWhere('phone', 'like', "%{$term}%")
           // full name match: handles “first last” search
           ->orWhereRaw(
               "CONCAT(COALESCE(first_name,''),' ',COALESCE(last_name,'')) LIKE ?",
               ["%{$term}%"]
           );
    });
}


        // Date range
        if ($request->filled('from')) {
            $query->where('created_at', '>=', $request->date('from')->startOfDay());
        }
        if ($request->filled('to')) {
            $query->where('created_at', '<=', $request->date('to')->endOfDay());
        }

        // Pagination
        $perPage = (int) $request->query('per_page', 25);
        $perPage = in_array($perPage, [10,25,50,100], true) ? $perPage : 25;

        $audits = $query->paginate($perPage)->appends($request->query());
        $events = ['created', 'updated', 'deleted', 'restored'];

        return view('admin.audit.index', compact('audits', 'events'));
    }

    public function show(Audit $audit)
    {
        return view('admin.audit.show', compact('audit'));
    }
}