<?php

namespace App\Http\Controllers\Admin\Addons;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Account;
use App\Models\Accounting\JournalItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class ProfitLossController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->start_date ? Carbon::parse($request->start_date) : now()->startOfMonth();
        $end = $request->end_date ? Carbon::parse($request->end_date) : now();

        $accounts = Account::whereIn('type', ['revenue', 'expense'])
            ->with('accountGroup')
            ->get()
            ->groupBy('type');

        $report = [];

        foreach ($accounts as $type => $groupedAccounts) {
            foreach ($groupedAccounts->groupBy(fn($a) => $a->accountGroup->name ?? 'Ungrouped') as $group => $accs) {
                foreach ($accs as $account) {
                    $amount = JournalItem::where('account_id', $account->id)
                        ->whereHas('journalEntry', fn($q) => $q->whereBetween('date', [$start, $end]))
                        ->select(DB::raw("SUM(CASE WHEN type = 'debit' THEN amount ELSE -amount END) as net"))
                        ->value('net') ?? 0;

                    if ($amount != 0) {
                        $report[$type][$group][] = [
                            'name' => $account->name,
                            'code' => $account->code,
                            'amount' => $amount,
                        ];
                    }
                }
            }
        }

        return view('addons.accounting.profit_loss', compact('report', 'start', 'end'));
    }

    public function monthly(Request $request)
    {
        $start = $request->start_date ? Carbon::parse($request->start_date) : now()->startOfYear();
        $end = $request->end_date ? Carbon::parse($request->end_date) : now();

        $monthlyReport = [];
        $months = [];

        // ✅ Generate full month list from Jan to selected end month
        $period = CarbonPeriod::create($start->copy()->startOfYear(), '1 month', $end);
        foreach ($period as $dt) {
            $key = $dt->format('Y-m'); // used in backend
            $label = $dt->format('F'); // shown in frontend
            $months[$key] = $label;
        }

        $journalItems = JournalItem::with(['account.accountGroup', 'journalEntry'])
            ->whereHas('journalEntry', fn($q) => $q->whereBetween('date', [$start, $end]))
            ->get();

        foreach ($journalItems as $item) {
            $monthKey = Carbon::parse($item->journalEntry->date)->format('Y-m');

            $type = $item->account->type;
            if (!in_array($type, ['revenue', 'expense'])) continue;

            $group = $item->account->accountGroup->name ?? 'Ungrouped';
            $account = $item->account->name;

            // Flip revenue as negative to match P&L convention
            $amount = $item->type === 'debit' ? $item->amount : -$item->amount;
            if ($type === 'revenue') $amount = -$amount;

            $monthlyReport[$type][$group][$account][$monthKey] = 
                ($monthlyReport[$type][$group][$account][$monthKey] ?? 0) + $amount;
        }

        return view('addons.accounting.profit_loss_monthly', compact('monthlyReport', 'months', 'start', 'end'));
    }


}