<?php

// app/Http/Controllers/Admin/Addons/AssetDepreciationController.php

namespace App\Http\Controllers\Admin\Addons;

use App\Http\Controllers\Controller;
use App\Models\Accounting\{Asset, Account};
use App\Services\JournalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Brian2694\Toastr\Facades\Toastr;

class AssetDepreciationController extends Controller
{
    /** Manual: run depreciation for ALL eligible assets for a given month (YYYY-MM) */
    public function runForMonth(Request $request, JournalService $journal)
    {
        $request->validate(['period' => ['required','date_format:Y-m']]);

        [$periodStart, $periodEnd, $periodKey] = $this->periodBounds($request->period);
        $count = 0;

        $assets = Asset::where('is_active', true)
            ->where('depreciation_method', 'straight_line')
            ->get();

        foreach ($assets as $asset) {
            $ok = $this->postOne($asset, $periodStart, $periodEnd, $periodKey, $journal);
            if ($ok) $count++;
        }

        Toastr::success(__("Depreciation posted for :n assets.", ['n' => $count]));
        return back();
    }

    /** Manual: run depreciation for ONE asset for a given month (YYYY-MM) */
    public function runForAsset(Request $request, Asset $asset, JournalService $journal)
    {
        $request->validate(['period' => ['required','date_format:Y-m']]);
        [$periodStart, $periodEnd, $periodKey] = $this->periodBounds($request->period);

        if ($this->postOne($asset, $periodStart, $periodEnd, $periodKey, $journal)) {
            Toastr::success(__('Depreciation posted.'));
        } else {
            Toastr::warning(__('Nothing to post (maybe already posted or not eligible).'));
        }

        return back();
    }

    // ---------- helpers ----------

    private function periodBounds(string $ym): array
    {
        $start = Carbon::createFromFormat('Y-m-d', $ym.'-01')->startOfDay();
        $end   = (clone $start)->endOfMonth()->endOfDay();
        $key   = $start->format('Y-m'); // normalized key like "2025-08"
        return [$start, $end, $key];
    }

    /** Core posting logic (idempotent). Returns true if a journal was posted. */
    private function postOne(Asset $asset, Carbon $periodStart, Carbon $periodEnd, string $periodKey, JournalService $journal): bool
    {
        if (!$asset->is_active || $asset->depreciation_method !== 'straight_line') {
            return false;
        }

        // skip assets purchased after this period
        if (Carbon::parse($asset->purchase_date)->startOfDay()->gt($periodEnd)) {
            return false;
        }

        // prevent duplicates (unique by asset+period)
        $exists = DB::table('acc_asset_depreciations')
            ->where('asset_id', $asset->id)
            ->where('period', $periodKey)
            ->exists();
        if ($exists) return false;

        // resolve accounts
        $expenseId = $this->resolveDepreciationExpenseAccountId();
        $accumId   = $this->resolveAccumulatedDepreciationAccountId();
        if (!$expenseId || !$accumId) return false;

        // compute monthly charge
        $depreciable = max(0, (float)$asset->cost - (float)($asset->salvage_value ?? 0));
        $monthsTotal = (int) $asset->useful_life_months ?: 0;
        if ($monthsTotal <= 0 || $depreciable <= 0) return false;

        $already = (float) DB::table('acc_asset_depreciations')
            ->where('asset_id', $asset->id)
            ->sum('amount');

        $remaining = max(0, round($depreciable - $already, 2));
        if ($remaining <= 0) return false;

        $perMonth = round($depreciable / $monthsTotal, 2);
        $amount   = min($perMonth, $remaining);

        if ($amount <= 0) return false;

        // post journal (date = periodEnd)
        $entry = $journal->create(
            [
                'date'        => $periodEnd->toDateString(),
                'type'        => 'depreciation',
                'reference'   => $asset->asset_code,
                'description' => 'Depreciation - '.$asset->asset_name.' ('.$periodKey.')',
            ],
            [
                ['account_id' => $expenseId, 'debit' => $amount, 'credit' => 0,       'memo' => 'Depreciation expense'],
                ['account_id' => $accumId,   'debit' => 0,       'credit' => $amount, 'memo' => 'Accumulated depreciation'],
            ]
        );

        // record to guard table
        DB::table('acc_asset_depreciations')->insert([
            'asset_id'         => $asset->id,
            'period'           => $periodKey, // normalized YYYY-MM
            'amount'           => $amount,
            'journal_entry_id' => $entry->id,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        return true;
    }

    private function resolveDepreciationExpenseAccountId(): ?int
    {
        return Account::where('type','expense')
            ->where(function($q){
                $q->where('name','like','%depreciation%')
                  ->orWhere('name','like','%depr%');
            })
            ->orderBy('code')->value('id');
    }

    private function resolveAccumulatedDepreciationAccountId(): ?int
    {
        return Account::where('type','asset')
            ->where(function($q){
                $q->where('name','like','%accumulated depreciation%')
                  ->orWhere('name','like','%accum depr%');
            })
            ->orderBy('code')->value('id');
    }
}