<?php

namespace App\Http\Controllers\Admin\Addons;

use App\Http\Controllers\Controller;
use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\JournalItem;
use App\Models\Accounting\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class JournalEntryController extends Controller
{
    public function index()
    {
        // Cache journal entries for 30 minutes
        $entries = Cache::remember('accounting.journal_entries', 30 * 60, function () {
            return JournalEntry::with('items.account')->latest()->get();
        });

        return view('addons.accounting.view_journals', compact('entries'));
    }

    public function show($id)
    {
        $entry = JournalEntry::with('items.account')->findOrFail($id);
        return view('addons.accounting.journal_show', compact('entry'));
    }

    public function create()
    {
        $accounts = Cache::rememberForever('accounting.accounts.dropdown', function () {
            return Account::select('id', DB::raw("CONCAT(code, ' - ', name) AS code_name"))->get();
        });

        do {
            $lastId = JournalEntry::max('id') + 1;
            $journal_number = '#JUR' . str_pad($lastId, 5, '0', STR_PAD_LEFT);
        } while (JournalEntry::where('journal_number', $journal_number)->exists());

        return view('addons.accounting.journal_form', compact('accounts', 'journal_number'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'journal_number' => 'required|unique:acc_journal_entries,journal_number',
            'date'           => 'required|date',
            'accounts'       => 'required|array|min:1',
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
            'date'           => $request->date,
            'description'    => $request->description,
            'reference'      => $request->reference,
        ]);

        foreach ($request->accounts as $row) {
            JournalItem::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $row['account_id'],
                'type'             => $row['debit'] > 0 ? 'debit' : 'credit',
                'amount'           => $row['debit'] > 0 ? $row['debit'] : $row['credit'],
                'description'      => $row['description'] ?? null,
            ]);
        }

        // Invalidate journal cache
        Cache::forget('accounting.journal_entries');

        return redirect()->route('admin.accounting.journals')->with('success', 'Journal entry created successfully.');
    }

    public function edit($id)
    {
        $entry = JournalEntry::with('items')->findOrFail($id);
        $accounts = Cache::rememberForever('accounting.accounts.dropdown', function () {
            return Account::select('id', DB::raw("CONCAT(code, ' - ', name) AS code_name"))->get();
        });

        return view('addons.accounting.journal_form', compact('entry', 'accounts'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'journal_number' => "required|unique:acc_journal_entries,journal_number,$id",
            'date'           => 'required|date',
            'accounts'       => 'required|array|min:1',
        ]);

        $entry = JournalEntry::findOrFail($id);
        $entry->update([
            'journal_number' => $request->journal_number,
            'date'           => $request->date,
            'description'    => $request->description,
            'reference'      => $request->reference,
        ]);

        // Delete old items
        $entry->items()->delete();

        foreach ($request->accounts as $row) {
            JournalItem::create([
                'journal_entry_id' => $entry->id,
                'account_id'       => $row['account_id'],
                'type'             => $row['debit'] > 0 ? 'debit' : 'credit',
                'amount'           => $row['debit'] > 0 ? $row['debit'] : $row['credit'],
                'description'      => $row['description'] ?? null,
            ]);
        }

        // Clear cache
        Cache::forget('accounting.journal_entries');

        return redirect()->route('admin.accounting.journals')->with('success', 'Journal entry updated successfully.');
    }

    public function destroy($id)
    {
        $entry = JournalEntry::findOrFail($id);
        $entry->delete();

        // Clear cache
        Cache::forget('accounting.journal_entries');

        return response()->json(['message' => 'Deleted successfully']);
    }
}
