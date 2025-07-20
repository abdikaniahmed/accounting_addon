<?php

namespace App\Http\Controllers\Admin\Addons;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LedgerController extends Controller
{
    public function index(Request $request)
    {
        // Cache the account list dropdown
        $accounts = Cache::rememberForever('accounting.ledger.accounts', function () {
            return Account::active()->select('id', 'name', 'code')->orderBy('name')->get();
        });

        $start = $request->start_date ?? now()->startOfMonth()->format('Y-m-d');
        $end = $request->end_date ?? now()->endOfMonth()->format('Y-m-d');
        $selectedAccountId = $request->account_id;

        $ledgers = [];

        // Filtered account(s)
        $queryAccounts = $selectedAccountId
            ? $accounts->where('id', $selectedAccountId)
            : $accounts;

        foreach ($queryAccounts as $account) {
            $transactions = $account->journalItems()
                ->with('journalEntry')
                ->whereHas('journalEntry', function ($q) use ($start, $end) {
                    $q->whereBetween('date', [$start, $end]);
                })
                ->orderBy('journal_entry_id')
                ->get();

            $ledgers[] = [
                'account' => $account,
                'transactions' => $transactions,
            ];
        }

        return view('addons.accounting.ledger_summary', compact(
            'accounts',
            'start',
            'end',
            'selectedAccountId',
            'ledgers'
        ));
    }
}
