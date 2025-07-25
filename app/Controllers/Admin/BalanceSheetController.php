<?php

namespace App\Http\Controllers\Admin\Addons;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Account;
use App\Models\Accounting\JournalItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PDF; // Barryvdh\DomPDF\Facade\Pdf;

class BalanceSheetController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->start_date ? Carbon::parse($request->start_date) : now()->startOfYear();
        $end = $request->end_date ? Carbon::parse($request->end_date) : now();
        $showAll = $request->has('show_all');

        $accounts = Account::with('accountGroup')->get()->groupBy('type');
        $balances = [];

        foreach ($accounts as $type => $groupedAccounts) {
            foreach ($groupedAccounts->groupBy(fn($a) => $a->accountGroup->name ?? 'Ungrouped') as $group => $accs) {
                foreach ($accs as $account) {
                    $balance = JournalItem::where('account_id', $account->id)
                        ->whereHas('journalEntry', function ($query) use ($start, $end) {
                            $query->whereBetween('date', [$start->toDateString(), $end->toDateString()]);
                        })
                        ->select(DB::raw("SUM(CASE WHEN type = 'debit' THEN amount ELSE -amount END) as balance"))
                        ->value('balance') ?? 0;

                    if ($showAll || $balance != 0) {
                        $balances[$type][$group][] = [
                            'name' => $account->name,
                            'code' => $account->code,
                            'balance' => $balance,
                        ];
                    }
                }
            }
        }

        return view('addons.accounting.balance_sheet', compact('balances', 'start', 'end', 'showAll'));
    }

    public function print(Request $request)
    {
        $data = $this->getBalanceData($request);
        return view('addons.accounting.balance_sheet_print', $data);
    }

    public function pdf(Request $request)
    {
        $data = $this->getBalanceData($request);
        $pdf = PDF::loadView('addons.accounting.balance_sheet_pdf', $data);
        return $pdf->download('balance_sheet.pdf');
    }

    private function getBalanceData(Request $request)
    {
        $start = $request->start_date ? \Carbon\Carbon::parse($request->start_date) : now()->startOfYear();
        $end = $request->end_date ? \Carbon\Carbon::parse($request->end_date) : now();
        $showAll = $request->has('show_all');

        $accounts = Account::with('accountGroup')->get()->groupBy('type');
        $balances = [];

        foreach ($accounts as $type => $groupedAccounts) {
            foreach ($groupedAccounts->groupBy(fn($a) => $a->accountGroup->name ?? 'Ungrouped') as $group => $accs) {
                foreach ($accs as $account) {
                    $balance = JournalItem::where('account_id', $account->id)
                        ->whereHas('journalEntry', fn($q) => $q->whereBetween('date', [$start, $end]))
                        ->select(DB::raw("SUM(CASE WHEN type = 'debit' THEN amount ELSE -amount END) as balance"))
                        ->value('balance') ?? 0;

                    if ($showAll || $balance != 0) {
                        $balances[$type][$group][] = [
                            'name' => $account->name,
                            'code' => $account->code,
                            'balance' => $balance,
                        ];
                    }
                }
            }
        }

        return compact('balances', 'start', 'end', 'showAll');
    }

}
