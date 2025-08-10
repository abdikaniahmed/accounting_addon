<?php

namespace App\Http\Controllers\Admin\Addons;

use App\Http\Controllers\Controller;
use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Services\JournalService;

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

        // Keep UI behavior (readonly journal number shown on the form)
        $journal_number = JournalEntry::nextNumber();

        return view('addons.accounting.journal_form', compact('accounts', 'journal_number'));
    }

    public function store(Request $request, JournalService $journal)
    {
        $request->validate([
            'journal_number' => 'required|unique:acc_journal_entries,journal_number',
            'date'           => 'required|date',
            'accounts'       => 'required|array|min:1',
        ]);

        // Build sanitized lines & validate balance
        [$lines, $balanced] = $this->buildLines($request->accounts);
        if (!$balanced) {
            return back()->with('error', __('Debit and Credit must be equal.'))->withInput();
        }

        // Persist via service (it will set journal_number safely too)
        $entry = $journal->create(
            [
                'date'        => $request->date,
                'type'        => 'manual',
                'reference'   => $request->reference,
                'description' => $request->description,
                // keep the chosen number from the form
                'journal_number' => $request->journal_number,
            ],
            $lines
        );

        Cache::forget('accounting.journal_entries');

        return redirect()
            ->route('admin.accounting.journals')
            ->with('success', __('Journal entry created successfully.'));
    }

    public function edit($id)
    {
        $entry = JournalEntry::with('items')->findOrFail($id);
        $accounts = Cache::rememberForever('accounting.accounts.dropdown', function () {
            return Account::select('id', DB::raw("CONCAT(code, ' - ', name) AS code_name"))->get();
        });

        return view('addons.accounting.journal_form', compact('entry', 'accounts'));
    }

    public function update(Request $request, $id, JournalService $journal)
    {
        $request->validate([
            'journal_number' => "required|unique:acc_journal_entries,journal_number,{$id}",
            'date'           => 'required|date',
            'accounts'       => 'required|array|min:1',
        ]);

        $entry = JournalEntry::findOrFail($id);

        [$lines, $balanced] = $this->buildLines($request->accounts);
        if (!$balanced) {
            return back()->with('error', __('Debit and Credit must be equal.'))->withInput();
        }

        // Replace atomically
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

        Cache::forget('accounting.journal_entries');

        return redirect()
            ->route('admin.accounting.journals')
            ->with('success', __('Journal entry updated successfully.'));
    }

    public function destroy($id, JournalService $journal)
    {
        $entry = JournalEntry::findOrFail($id);
        $journal->delete($entry);

        Cache::forget('accounting.journal_entries');

        return response()->json(['message' => __('Deleted successfully')]);
    }

    /**
     * Normalize incoming rows to the service format and check balance.
     *
     * @param  array $rows
     * @return array [array $lines, bool $balanced]
     */
    private function buildLines(array $rows): array
    {
        $totalDebit  = 0.0;
        $totalCredit = 0.0;
        $lines = [];

        foreach ($rows as $row) {
            $debit  = (float) ($row['debit']  ?? 0);
            $credit = (float) ($row['credit'] ?? 0);

            if ($debit <= 0 && $credit <= 0) {
                // skip empty rows
                continue;
            }

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