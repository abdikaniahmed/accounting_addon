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

        // New: depreciation at month end 00:30 (app timezone)
        $schedule->command('depreciation:post')
            ->when(fn() => (bool) (config('accounting.auto_depreciation', true))) // or use a DB setting
            ->monthlyOn(now()->endOfMonth()->day, '00:30');
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}