<?php

namespace App\Http\Controllers\Seller\Addons;

use App\Http\Controllers\Controller;
use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Services\JournalService;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;

class JournalEntryController extends Controller
{
    public function index()
    {
        $sellerId = (int) optional(Sentinel::getUser())->id;

        $entries = Cache::remember('accounting.journal_entries.seller.'.$sellerId, 30*60, function () {
            return JournalEntry::onlyOwn()->with('journalItems.account')->latest()->get();
        });

        return view('addons.accountingSeller.view_journals', compact('entries'));
    }

    public function show($id)
    {
        $entry = JournalEntry::onlyOwn()->with('journalItems.account')->findOrFail($id);
        return view('addons.accountingSeller.journal_show', compact('entry'));
    }

    public function create()
    {
        $sellerId = (int) optional(Sentinel::getUser())->id;

        $accounts = Cache::rememberForever('accounting.accounts.dropdown.seller.'.$sellerId, function () {
            return Account::onlyOwn()
                ->select('id', DB::raw("CONCAT(COALESCE(code,''), CASE WHEN code IS NULL OR code='' THEN '' ELSE ' - ' END, name) AS code_name"))
                ->orderBy('name')
                ->get();
        });

        $journal_number = JournalEntry::nextNumber();

        return view('addons.accountingSeller.journal_form', compact('accounts', 'journal_number'));
    }

    public function store(Request $request, JournalService $journal)
    {
        $request->validate([
            'journal_number' => 'required|unique:acc_journal_entries,journal_number',
            'date'           => 'required|date',
            'accounts'       => 'required|array|min:1',
        ]);

        [$lines, $balanced] = $this->buildLines($request->accounts);
        if (!$balanced) {
            return back()->with('error', __('Debit and Credit must be equal.'))->withInput();
        }

        $entry = $journal->create(
            [
                'date'           => $request->date,
                'type'           => 'manual',
                'reference'      => $request->reference,
                'description'    => $request->description,
                'journal_number' => $request->journal_number,
            ],
            $lines
        );

        return redirect()->route('seller.accounting.journals')
            ->with('success', __('Journal entry created successfully.'));
    }

    public function edit($id)
    {
        $entry = JournalEntry::onlyOwn()->with('journalItems')->findOrFail($id);

        $sellerId = (int) optional(Sentinel::getUser())->id;

        $accounts = Cache::rememberForever('accounting.accounts.dropdown.seller.'.$sellerId, function () {
            return Account::onlyOwn()
                ->select('id', DB::raw("CONCAT(COALESCE(code,''), CASE WHEN code IS NULL OR code='' THEN '' ELSE ' - ' END, name) AS code_name"))
                ->orderBy('name')
                ->get();
        });

        return view('addons.accountingSeller.journal_form', compact('entry', 'accounts'));
    }

    public function update(Request $request, $id, JournalService $journal)
    {
        $request->validate([
            'journal_number' => "required|unique:acc_journal_entries,journal_number,{$id}",
            'date'           => 'required|date',
            'accounts'       => 'required|array|min:1',
        ]);

        $entry = JournalEntry::onlyOwn()->findOrFail($id);

        [$lines, $balanced] = $this->buildLines($request->accounts);
        if (!$balanced) {
            return back()->with('error', __('Debit and Credit must be equal.'))->withInput();
        }

        $journal->replace(
            $entry,
            [
                'date'           => $request->date,
                'type'           => $entry->type ?: 'manual',
                'reference'      => $request->reference,
                'description'    => $request->description,
                'journal_number' => $request->journal_number,
            ],
            $lines
        );

        return redirect()->route('seller.accounting.journals')
            ->with('success', __('Journal entry updated successfully.'));
    }

    public function destroy($id, JournalService $journal)
    {
        $entry = JournalEntry::onlyOwn()->findOrFail($id);
        $journal->delete($entry);

        return response()->json(['message' => __('Deleted successfully')]);
    }

    private function buildLines(array $rows): array
    {
        $totalDebit  = 0.0;
        $totalCredit = 0.0;
        $lines = [];

        foreach ($rows as $row) {
            $debit  = (float) ($row['debit']  ?? 0);
            $credit = (float) ($row['credit'] ?? 0);
            if ($debit <= 0 && $credit <= 0) continue;

            $totalDebit  += $debit;
            $totalCredit += $credit;

            $lines[] = [
                'account_id' => (int) $row['account_id'],
                'debit'      => $debit,
                'credit'     => $credit,
                'memo'       => $row['description'] ?? null,
            ];
        }

        return [$lines, abs($totalDebit - $totalCredit) < 0.00001];
    }
}