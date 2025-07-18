<?php

namespace App\Http\Controllers\Admin\Addons;

use App\Http\Controllers\Controller;
use App\Models\Accounting\JournalEntry;

class JournalEntryController extends Controller
{
    public function index()
    {
        $entries = JournalEntry::with('items')->latest()->get();
        return view('addons.accounting.journals', compact('entries'));
    }
}
