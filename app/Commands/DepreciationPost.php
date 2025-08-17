<?php
// app/Console/Commands/DepreciationPost.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Accounting\{Asset, Account};
use App\Services\JournalService;

class DepreciationPost extends Command
{
    protected $signature = 'depreciation:post {--date=} {--dry-run}';
    protected $description = 'Post monthly depreciation for all active assets (idempotent).';

    public function handle(): int
    {
        $target = $this->option('date')
            ? Carbon::parse($this->option('date'))->endOfMonth()
            : now()->endOfMonth();

        // Calculate period key like 2025-08
        $period = $target->format('Y-m');

        // Ensure guard tables exist (skip silently if you haven’t created the helper table)
        if (!DB::getSchemaBuilder()->hasTable('acc_asset_depreciations')) {
            $this->warn('acc_asset_depreciations table not found. Skipping (create it to enable idempotency).');
            return 0;
        }

        // Pull required accounts (you may hardcode/setting these)
        $accDepreciationExpense = Account::where('name','like','%depreciation%')->where('type','expense')->value('id');
        $accAccumulatedDep      = Account::where('name','like','%accumulated depreciation%')->where('type','asset')->value('id');

        if (!$accDepreciationExpense || !$accAccumulatedDep) {
            $this->error('Missing Depreciation Expense or Accumulated Depreciation account.');
            return 1;
        }

        $assets = Asset::query()
            ->where('is_active', true)
            ->where('depreciation_method', 'straight_line')
            ->whereNotNull('useful_life_months')
            ->get();

        $posted = 0;

        foreach ($assets as $asset) {
            // Idempotency: skip if already posted for this asset+period
            $already = DB::table('acc_asset_depreciations')
                ->where('asset_id', $asset->id)
                ->where('period', $period)
                ->exists();

            if ($already) {
                $this->line("Skip {$asset->asset_name} ({$period}) – already posted.");
                continue;
            }

            // Straight-line monthly amount
            $salvage = (float)($asset->salvage_value ?? 0);
            $base    = max(0, (float)$asset->cost - $salvage);
            $months  = max(1, (int)$asset->useful_life_months);
            $amount  = round($base / $months, 2);

            if ($amount <= 0) continue;

            if ($this->option('dry-run')) {
                $this->info("Would post {$period} depreciation for {$asset->asset_name}: {$amount}");
                continue;
            }

            // Post journal (Dr Expense, Cr Accumulated Depreciation)
            /** @var JournalService $journal */
            $journal = app(JournalService::class);

            $entry = $journal->create(
                [
                    'date'        => $target->toDateString(),
                    'type'        => 'depreciation',
                    'reference'   => "{$asset->asset_code} / {$period}",
                    'description' => "Monthly depreciation - {$asset->asset_name} ({$period})",
                ],
                [
                    ['account_id' => $accDepreciationExpense, 'debit' => $amount, 'credit' => 0,       'memo' => 'Depreciation expense'],
                    ['account_id' => $accAccumulatedDep,      'debit' => 0,       'credit' => $amount, 'memo' => 'Accumulated depreciation'],
                ]
            );

            // Record guard row (unique per asset+period)
            DB::table('acc_asset_depreciations')->insert([
                'asset_id'          => $asset->id,
                'period'            => $period,
                'amount'            => $amount,
                'journal_entry_id'  => $entry->id,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            $posted++;
        }

        $this->info("Depreciation posted for {$posted} asset(s) for period {$period}.");
        return 0;
    }
}