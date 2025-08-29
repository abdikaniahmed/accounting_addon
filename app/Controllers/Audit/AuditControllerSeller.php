<?php

// app/Http/Controllers/Seller/Addons/AuditController.php
namespace App\Http\Controllers\Seller\Addons;

use App\Http\Controllers\Controller;
use OwenIt\Auditing\Models\Audit;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Illuminate\Http\Request;

class SellerAuditController extends Controller
{
    public function index(Request $req)
    {
        $sellerId = (int) optional(Sentinel::getUser())->id;

        $q = Audit::query()
            // JSON array column "tags": look for "seller:{id}"
            ->whereJsonContains('tags', "seller:$sellerId")
            ->latest();

        if ($ev = $req->event)         $q->where('event', $ev);
        if ($model = $req->model)      $q->where('auditable_type', 'like', "%$model%");
        if ($from = $req->from)        $q->whereDate('created_at','>=',$from);
        if ($to   = $req->to)          $q->whereDate('created_at','<=',$to);

        $perPage = (int) $req->get('per_page', 25);
        $audits  = $q->paginate($perPage)->appends($req->query());

        $events = ['created','updated','deleted','restored'];

        return view('addons.accountingSeller.audit.index', compact('audits','events'));
    }

    public function show($id)
    {
        $sellerId = (int) optional(Sentinel::getUser())->id;

        $audit = Audit::where('id', $id)
            ->whereJsonContains('tags', "seller:$sellerId")
            ->firstOrFail();

        return view('addons.accountingSeller.audit.show', compact('audit'));
    }
}