<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Addons\JournalEntryController;
use App\Http\Controllers\Admin\Addons\ChartOfAccountController;
use App\Http\Controllers\Admin\Addons\LedgerController;
use App\Http\Controllers\Admin\Addons\AccountGroupController;
use App\Http\Controllers\Admin\Addons\BalanceSheetController;
use App\Http\Controllers\Admin\Addons\ProfitLossController;
use App\Http\Controllers\Admin\Addons\BankAccountController;
use App\Http\Controllers\Admin\Addons\BankTransferController;
use App\Http\Controllers\Admin\Addons\CustomerController;

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
           
            //Balance Sheet 
            Route::get('/balance-sheet', [BalanceSheetController::class, 'index'])->name('admin.accounting.balance_sheet');
            Route::get('/balance-sheet/print', [BalanceSheetController::class, 'print'])->name('admin.accounting.balance_sheet.print');
            Route::get('/balance-sheet/pdf', [BalanceSheetController::class, 'pdf'])->name('admin.accounting.balance_sheet.pdf');
           
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

            // Profit & Loss Report
            Route::get('/profit-loss', [ProfitLossController::class, 'index'])->name('admin.accounting.profit_loss');
            Route::get('/profit-loss/monthly', [ProfitLossController::class, 'monthly'])->name('admin.accounting.profit_loss.monthly');
            Route::get('/profit-loss/print', [ProfitLossController::class, 'print'])->name('admin.accounting.profit_loss.print');
            Route::get('/profit-loss/pdf', [ProfitLossController::class, 'pdf'])->name('admin.accounting.profit_loss.pdf');

           // Banks 
            Route::get('bank-accounts', [BankAccountController::class, 'index'])->name('admin.accounting.bank_accounts.index');
            Route::post('bank-accounts', [BankAccountController::class, 'store'])->name('admin.accounting.bank_accounts.store');
            Route::delete('bank-accounts/{id}', [BankAccountController::class, 'destroy'])->name('admin.accounting.bank_account.destroy');

            Route::get('/transfers', [BankTransferController::class, 'index'])->name('admin.accounting.transfers.index');
            Route::get('/transfers/create', [BankTransferController::class, 'create'])->name('admin.accounting.transfers.create');
            Route::post('/transfers', [BankTransferController::class, 'store'])->name('admin.accounting.transfers.store');

            // Customers
            Route::get('/customers', [CustomerController::class, 'index'])->name('admin.accounting.customers.index');
            Route::get('/customers/create', [CustomerController::class, 'create'])->name('admin.accounting.customers.create');
            Route::post('/customers', [CustomerController::class, 'store'])->name('admin.accounting.customers.store');
            Route::get('/customers/{id}/edit', [CustomerController::class, 'edit'])->name('admin.accounting.customers.edit');
            Route::put('/customers/{id}', [CustomerController::class, 'update'])->name('admin.accounting.customers.update');
            Route::delete('/customers/{id}', [CustomerController::class, 'destroy'])->name('admin.accounting.customers.destroy');

        });
    });
});