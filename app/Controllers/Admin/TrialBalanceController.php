<?php

namespace App\Http\Controllers\Admin\Addons;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TrialBalanceController extends Controller
{
    public function index(Request $request)
    {
        $start = Carbon::parse($request->get('start_date', now()->startOfMonth()->format('Y-m-d')))->startOfDay();
        $end   = Carbon::parse($request->get('end_date', now()->format('Y-m-d')))->endOfDay();
        $showZero = $request->boolean('show_zero', false);

        $accounts = DB::table('acc_accounts')
            ->select('id', 'code', 'name', 'type')
            ->orderByRaw('CASE WHEN code IS NULL OR code = "" THEN 1 ELSE 0 END')
            ->orderBy('code')->orderBy('name')
            ->get();

        // Opening
        $opening = DB::table('acc_journal_items as ji')
            ->join('acc_journal_entries as je', 'je.id', '=', 'ji.journal_entry_id')
            ->whereDate('je.date', '<', $start->toDateString())
            ->groupBy('ji.account_id')
            ->selectRaw("
                ji.account_id,
                SUM(CASE WHEN ji.type = 'debit'  THEN ji.amount ELSE 0 END) AS debit,
                SUM(CASE WHEN ji.type = 'credit' THEN ji.amount ELSE 0 END) AS credit
            ")->get()->keyBy('account_id');

        // Period
        $period = DB::table('acc_journal_items as ji')
            ->join('acc_journal_entries as je', 'je.id', '=', 'ji.journal_entry_id')
            ->whereDate('je.date', '>=', $start->toDateString())
            ->whereDate('je.date', '<=', $end->toDateString())
            ->groupBy('ji.account_id')
            ->selectRaw("
                ji.account_id,
                SUM(CASE WHEN ji.type = 'debit'  THEN ji.amount ELSE 0 END) AS debit,
                SUM(CASE WHEN ji.type = 'credit' THEN ji.amount ELSE 0 END) AS credit
            ")->get()->keyBy('account_id');

        $rows = [];
        $tot = [
            'open_debit' => 0, 'open_credit' => 0,
            'mov_debit'  => 0, 'mov_credit'  => 0,
            'close_debit'=> 0, 'close_credit'=> 0,
        ];

        foreach ($accounts as $a) {
            $o = $opening->get($a->id);
            $p = $period->get($a->id);

            $open_debit  = (float)($o->debit  ?? 0);
            $open_credit = (float)($o->credit ?? 0);
            $mov_debit   = (float)($p->debit  ?? 0);
            $mov_credit  = (float)($p->credit ?? 0);

            $open_net  = $open_debit - $open_credit;
            $close_net = $open_net + ($mov_debit - $mov_credit);

            $row = (object)[
                'id'          => $a->id,
                'code'        => $a->code,
                'name'        => $a->name,
                'type'        => $a->type,
                'open_debit'  => $open_net > 0 ? $open_net : 0,
                'open_credit' => $open_net < 0 ? -$open_net : 0,
                'mov_debit'   => $mov_debit,
                'mov_credit'  => $mov_credit,
                'close_debit'  => $close_net > 0 ? $close_net : 0,
                'close_credit' => $close_net < 0 ? -$close_net : 0,
            ];

            $tot['open_debit']   += $row->open_debit;
            $tot['open_credit']  += $row->open_credit;
            $tot['mov_debit']    += $row->mov_debit;
            $tot['mov_credit']   += $row->mov_credit;
            $tot['close_debit']  += $row->close_debit;
            $tot['close_credit'] += $row->close_credit;

            if ($showZero || $row->open_debit || $row->open_credit || $row->mov_debit || $row->mov_credit || $row->close_debit || $row->close_credit) {
                $rows[] = $row;
            }
        }

        foreach ($tot as $k => $v) $tot[$k] = round($v, 2);

        return view('addons.accounting.trial_balance', [
            'rows'      => $rows,
            'tot'       => $tot,
            'start'     => $start->toDateString(),
            'end'       => $end->toDateString(),
            'showZero'  => $showZero,
        ]);
    }
}