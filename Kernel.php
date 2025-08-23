<?php
// app\Console\Kernel.php
namespace App\Console;

use App\Console\Commands\AllClear;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        AllClear::class
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('subscription:check')->when(function (){
            if (addon_is_activated('seller_subscription') && settingHelper('seller_system') == 1) {
                return true;
            }

            return false;
        })->everyFifteenMinutes();

        // Recommended: run on 1st and let command post for the month implied by --date (or default to last month end)
        $schedule->command('depreciation:post')
            ->when(fn () => addon_is_activated('accounting_addon') && (int) settingHelper('accounting_depr_auto') === 1)
            ->monthlyOn(1, '00:30');
        }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}