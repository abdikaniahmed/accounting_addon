<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Addons\JournalEntryController;
use App\Http\Controllers\Admin\Addons\ChartOfAccountController;

Route::middleware(['XSS','isInstalled'])->group(function () {
    Route::group([
        'prefix' => LaravelLocalization::setLocale(),
        'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath','isInstalled']
    ], function () {

        Route::middleware(['adminCheck','loginCheck'])->prefix('admin/accounting')->group(function () {

            Route::get('/journals', [JournalEntryController::class, 'index'])
                ->name('admin.accounting.journals');

            Route::get('/chart-of-accounts', [ChartOfAccountController::class, 'index'])
                ->name('admin.accounting.coa');

            Route::get('/chart-of-accounts/create', [ChartOfAccountController::class, 'create'])
                ->name('admin.accounting.coa.create');

            Route::post('/chart-of-accounts/store', [ChartOfAccountController::class, 'store'])
                ->name('admin.accounting.coa.store');
        });
    });
});
