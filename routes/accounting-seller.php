<?php
// routes/accounting-seller.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Seller\Addons\AccountGroupController as SellerAccountGroupController;
use App\Http\Controllers\Seller\Addons\SellerAuditController as SellerAuditController;
use App\Http\Controllers\Seller\Addons\ChartOfAccountController as SellerCoa;
use App\Http\Controllers\Seller\Addons\JournalEntryController as SellerJournalController;

Route::middleware(['XSS','isInstalled'])->group(function () {
    Route::group([
        'prefix' => LaravelLocalization::setLocale(),
        'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath', 'isInstalled']
    ], function () {

        Route::middleware(['sellerCheck','loginCheck'])
            ->prefix('seller/accounting')
            ->middleware('PermissionCheck:accounting_access') // parent gate
            ->group(function () {

            /** -------- Account Groups (Seller) -------- **/
            Route::get('groups', [SellerAccountGroupController::class, 'index'])
                ->name('seller.accounting.groups.index')
                ->middleware('PermissionCheck:accounting_group_read');

            Route::get('groups/create', [SellerAccountGroupController::class, 'create'])
                ->name('seller.accounting.groups.create')
                ->middleware('PermissionCheck:accounting_group_create');

            Route::post('groups', [SellerAccountGroupController::class, 'store'])
                ->name('seller.accounting.groups.store')
                ->middleware('PermissionCheck:accounting_group_create');

            Route::get('groups/{id}/edit', [SellerAccountGroupController::class, 'edit'])
                ->name('seller.accounting.groups.edit')
                ->middleware('PermissionCheck:accounting_group_update');

            Route::put('groups/{id}', [SellerAccountGroupController::class, 'update'])
                ->name('seller.accounting.groups.update')
                ->middleware('PermissionCheck:accounting_group_update');

            Route::delete('groups/{id}', [SellerAccountGroupController::class, 'destroy'])
                ->name('seller.accounting.groups.destroy')
                ->middleware('PermissionCheck:accounting_group_delete');

            Route::get('groups/import', [SellerAccountGroupController::class, 'importView'])
                ->name('seller.accounting.groups.import.view')
                ->middleware('PermissionCheck:accounting_group_import');

            Route::post('groups/import', [SellerAccountGroupController::class, 'import'])
                ->name('seller.accounting.groups.import')
                ->middleware('PermissionCheck:accounting_group_import');

            Route::get('groups/sample-download', function () {
                $path = public_path('excel/account_group_import_sample.xlsx');
                return response()->download($path, 'account_group_import_sample.xlsx');
            })->name('seller.accounting.groups.sample.download')
              ->middleware('PermissionCheck:accounting_group_import');

            /** -------- Chart Of Accounts (Seller) -------- **/
            Route::get('chart-of-accounts', [SellerCoa::class,'index'])
                ->name('seller.accounting.coa.index')
                ->middleware('PermissionCheck:accounting_coa_read');

            Route::get('chart-of-accounts/create', [SellerCoa::class,'create'])
                ->name('seller.accounting.coa.create')
                ->middleware('PermissionCheck:accounting_coa_create');

            Route::post('chart-of-accounts', [SellerCoa::class,'store'])
                ->name('seller.accounting.coa.store')
                ->middleware('PermissionCheck:accounting_coa_create');

            Route::get('chart-of-accounts/{id}/edit', [SellerCoa::class,'edit'])
                ->name('seller.accounting.coa.edit')
                ->middleware('PermissionCheck:accounting_coa_update');

            Route::put('chart-of-accounts/{id}', [SellerCoa::class,'update'])
                ->name('seller.accounting.coa.update')
                ->middleware('PermissionCheck:accounting_coa_update');

            Route::delete('chart-of-accounts/{id}', [SellerCoa::class,'destroy'])
                ->name('seller.accounting.coa.destroy')
                ->middleware('PermissionCheck:accounting_coa_delete');

            Route::get('chart-of-accounts/import', [SellerCoa::class,'importView'])
                ->name('seller.accounting.coa.import.view')
                ->middleware('PermissionCheck:accounting_coa_import');

            Route::post('chart-of-accounts/import', [SellerCoa::class,'import'])
                ->name('seller.accounting.coa.import')
                ->middleware('PermissionCheck:accounting_coa_import');

             // Journals (Seller) — protected by PermissionCheck
            Route::get('journals',                [SellerJournalController::class, 'index'])
                ->name('seller.accounting.journals')
                ->middleware('PermissionCheck:accounting_journal_read');

            Route::get('journals/create',         [SellerJournalController::class, 'create'])
                ->name('seller.accounting.journals.create')
                ->middleware('PermissionCheck:accounting_journal_create');

            Route::post('journals',               [SellerJournalController::class, 'store'])
                ->name('seller.accounting.journals.store')
                ->middleware('PermissionCheck:accounting_journal_create');

            Route::get('journals/{id}',           [SellerJournalController::class, 'show'])
                ->name('seller.accounting.journals.show')
                ->middleware('PermissionCheck:accounting_journal_read');

            Route::get('journals/{id}/edit',      [SellerJournalController::class, 'edit'])
                ->name('seller.accounting.journals.edit')
                ->middleware('PermissionCheck:accounting_journal_update');

            Route::put('journals/{id}',           [SellerJournalController::class, 'update'])
                ->name('seller.accounting.journals.update')
                ->middleware('PermissionCheck:accounting_journal_update');

            Route::delete('journals/{id}',        [SellerJournalController::class, 'destroy'])
                ->name('seller.accounting.journals.destroy')
                ->middleware('PermissionCheck:accounting_journal_delete');
       

            /** ---------------- Audits (Seller) ---------------- **/
            Route::get('/audits', [SellerAuditController::class, 'index'])
                ->name('seller.audits.index')
                ->middleware('PermissionCheck:audit_read');

            Route::get('/audits/{audit}', [SellerAuditController::class, 'show'])
                ->name('seller.audits.show')
                ->middleware('PermissionCheck:audit_view');
        });
    });
});