<?php

namespace App\Http\Controllers\Admin\Addons;

use App\Http\Controllers\Controller;
use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\JournalItem;
use App\Models\Accounting\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JournalEntryController extends Controller
{
    public function index()
    {
        $entries = JournalEntry::with('items.account')->latest()->get();
        return view('addons.accounting.journals', compact('entries'));
    }

    public function show($id)
    {
        $entry = JournalEntry::with('items.account')->findOrFail($id);
        return view('addons.accounting.journal_show', compact('entry'));
    }

    public function create()
    {
        $accounts = Account::select('id', DB::raw("CONCAT(code, ' - ', name) AS code_name"))->get();

        // Simple journal number (could improve with DB max or UUID)
        $lastId = JournalEntry::max('id') + 1;
        $journal_number = '#JUR' . str_pad($lastId, 5, '0', STR_PAD_LEFT);

        return view('addons.accounting.journal_form', compact('accounts', 'journal_number'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'accounts' => 'required|array|min:1'
        ]);

        $totalDebit = 0;
        $totalCredit = 0;
        foreach ($request->accounts as $row) {
            $totalDebit += floatval($row['debit'] ?? 0);
            $totalCredit += floatval($row['credit'] ?? 0);
        }

        if ($totalDebit != $totalCredit) {
            return redirect()->back()->with('error', 'Debit and Credit must be equal.');
        }

        $entry = JournalEntry::create([
            'journal_number' => $request->journal_number,
            'date' => $request->date,
            'description' => $request->description,
            'reference' => $request->reference,
        ]);

        foreach ($request->accounts as $row) {
            JournalItem::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $row['account_id'],
                'type'             => $row['debit'] > 0 ? 'debit' : 'credit',
                'amount'           => $row['debit'] > 0 ? $row['debit'] : $row['credit'],
            ]);
        }

        return redirect()->route('admin.accounting.journals')->with('success', 'Journal entry created successfully.');
    }
}
