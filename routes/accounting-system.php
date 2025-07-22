<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Addons\JournalEntryController;
use App\Http\Controllers\Admin\Addons\ChartOfAccountController;
use App\Http\Controllers\Admin\Addons\LedgerController;
use App\Http\Controllers\Admin\Addons\AccountGroupController;

Route::middleware(['XSS','isInstalled'])->group(function () {
    Route::group([
        'prefix' => LaravelLocalization::setLocale(),
        'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath', 'isInstalled']
    ], function () {

        Route::middleware(['adminCheck', 'loginCheck'])->prefix('admin/accounting')->group(function () {

            // Journal Entries
            Route::get('/journals', [JournalEntryController::class, 'index'])->name('admin.accounting.journals');
            Route::get('/journals/create', [JournalEntryController::class, 'create'])->name('admin.accounting.journals.create');
            Route::post('/journals', [JournalEntryController::class, 'store'])->name('admin.accounting.journals.store');
            Route::get('/journals/{id}', [JournalEntryController::class, 'show'])->name('admin.accounting.journals.show');
            Route::get('/journals/{id}/edit', [JournalEntryController::class, 'edit'])->name('admin.accounting.journals.edit');
            Route::put('/journals/{id}', [JournalEntryController::class, 'update'])->name('admin.accounting.journals.update');
            Route::delete('/journals/{id}', [JournalEntryController::class, 'destroy'])->name('admin.accounting.journals.destroy');

            // Chart of Accounts
            Route::get('/chart-of-accounts', [ChartOfAccountController::class, 'index'])->name('admin.accounting.coa');
            Route::get('/chart-of-accounts/create', [ChartOfAccountController::class, 'create'])->name('admin.accounting.coa.create');
            Route::post('/chart-of-accounts', [ChartOfAccountController::class, 'store'])->name('admin.accounting.coa.store');
            Route::get('/chart-of-accounts/{id}/edit', [ChartOfAccountController::class, 'edit'])->name('admin.accounting.coa.edit');
            Route::put('/chart-of-accounts/{id}', [ChartOfAccountController::class, 'update'])->name('admin.accounting.coa.update');
            Route::delete('/chart-of-accounts/{id}', [ChartOfAccountController::class, 'destroy'])->name('admin.accounting.coa.destroy');
            Route::get('chart-of-accounts/import', [ChartOfAccountController::class, 'importView'])->name('admin.accounting.coa.import.view');
            Route::post('chart-of-accounts/import', [ChartOfAccountController::class, 'import'])->name('admin.accounting.coa.import');
            Route::get('accounting/coa/sample-download', function () {
                $path = public_path('excel/chart_of_accounts_import_sample.xlsx');
                return response()->download($path, 'chart_of_accounts_import_sample.xlsx');
            })->name('admin.accounting.coa.sample.download');



            // Ledger Summary
            Route::get('/ledger', [LedgerController::class, 'index'])->name('admin.accounting.ledger');

            // Account Groups
            Route::get('groups', [AccountGroupController::class, 'index'])->name('admin.accounting.groups.index');
            Route::get('groups/create', [AccountGroupController::class, 'create'])->name('admin.accounting.groups.create');
            Route::post('groups', [AccountGroupController::class, 'store'])->name('admin.accounting.groups.store');
            Route::get('groups/{id}/edit', [AccountGroupController::class, 'edit'])->name('admin.accounting.groups.edit');
            Route::put('groups/{id}', [AccountGroupController::class, 'update'])->name('admin.accounting.groups.update');
            Route::delete('groups/{id}', [AccountGroupController::class, 'destroy'])->name('admin.accounting.groups.destroy');

            // Account Groups Import
            Route::get('groups/import', [AccountGroupController::class, 'importView'])->name('admin.accounting.groups.import.view');
            Route::post('groups/import', [AccountGroupController::class, 'import'])->name('admin.accounting.groups.import');
            Route::get('accounting/groups/sample-download', function () {
                $path = public_path('excel/account_group_import_sample.xlsx');
                return response()->download($path, 'account_group_import_sample.xlsx');
            })->name('admin.accounting.groups.sample.download');

        });
    });
});
