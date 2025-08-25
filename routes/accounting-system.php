<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Addons\{
    JournalEntryController,
    ChartOfAccountController,
    LedgerController,
    AccountGroupController,
    BalanceSheetController,
    ProfitLossController,
    BankAccountController,
    BankTransferController,
    CustomerController,
    VendorController,
    QuickExpensesController,
    BillController,
    BillPaymentController,
    TrialBalanceController,
    AssetController,
    AssetDepreciationController,
    AuditController
};

Route::middleware(['XSS','isInstalled'])->group(function () {
    Route::group([
        'prefix' => LaravelLocalization::setLocale(),
        'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath', 'isInstalled']
    ], function () {

        Route::middleware(['adminCheck', 'loginCheck'])
            ->prefix('admin/accounting')
            // Global Accounting gate (controls menu visibility too)
            ->middleware(['PermissionCheck:accounting_access'])
            ->group(function () {

            /** ---------------- Journals ---------------- **/
            Route::get('/journals', [JournalEntryController::class, 'index'])
                ->name('admin.accounting.journals')
                ->middleware('PermissionCheck:accounting_journal_read');

            Route::get('/journals/create', [JournalEntryController::class, 'create'])
                ->name('admin.accounting.journals.create')
                ->middleware('PermissionCheck:accounting_journal_create');

            Route::post('/journals', [JournalEntryController::class, 'store'])
                ->name('admin.accounting.journals.store')
                ->middleware('PermissionCheck:accounting_journal_create');

            Route::get('/journals/{id}', [JournalEntryController::class, 'show'])
                ->name('admin.accounting.journals.show')
                ->middleware('PermissionCheck:accounting_journal_read');

            Route::get('/journals/{id}/edit', [JournalEntryController::class, 'edit'])
                ->name('admin.accounting.journals.edit')
                ->middleware('PermissionCheck:accounting_journal_update');

            Route::put('/journals/{id}', [JournalEntryController::class, 'update'])
                ->name('admin.accounting.journals.update')
                ->middleware('PermissionCheck:accounting_journal_update');

            Route::delete('/journals/{id}', [JournalEntryController::class, 'destroy'])
                ->name('admin.accounting.journals.destroy')
                ->middleware('PermissionCheck:accounting_journal_delete');

            /** ------------- Chart of Accounts ------------- **/
            Route::get('/chart-of-accounts', [ChartOfAccountController::class, 'index'])
                ->name('admin.accounting.coa')
                ->middleware('PermissionCheck:accounting_coa_read');

            Route::get('/chart-of-accounts/create', [ChartOfAccountController::class, 'create'])
                ->name('admin.accounting.coa.create')
                ->middleware('PermissionCheck:accounting_coa_create');

            Route::post('/chart-of-accounts', [ChartOfAccountController::class, 'store'])
                ->name('admin.accounting.coa.store')
                ->middleware('PermissionCheck:accounting_coa_create');

            Route::get('/chart-of-accounts/{id}/edit', [ChartOfAccountController::class, 'edit'])
                ->name('admin.accounting.coa.edit')
                ->middleware('PermissionCheck:accounting_coa_update');

            Route::put('/chart-of-accounts/{id}', [ChartOfAccountController::class, 'update'])
                ->name('admin.accounting.coa.update')
                ->middleware('PermissionCheck:accounting_coa_update');

            Route::delete('/chart-of-accounts/{id}', [ChartOfAccountController::class, 'destroy'])
                ->name('admin.accounting.coa.destroy')
                ->middleware('PermissionCheck:accounting_coa_delete');

            Route::get('chart-of-accounts/import', [ChartOfAccountController::class, 'importView'])
                ->name('admin.accounting.coa.import.view')
                ->middleware('PermissionCheck:accounting_coa_import');

            Route::post('chart-of-accounts/import', [ChartOfAccountController::class, 'import'])
                ->name('admin.accounting.coa.import')
                ->middleware('PermissionCheck:accounting_coa_import');

            Route::get('accounting/coa/sample-download', function () {
                $path = public_path('excel/chart_of_accounts_import_sample.xlsx');
                return response()->download($path, 'chart_of_accounts_import_sample.xlsx');
            })->name('admin.accounting.coa.sample.download')
              ->middleware('PermissionCheck:accounting_coa_import');

            /** ---------------- Ledger ---------------- **/
            Route::get('/ledger', [LedgerController::class, 'index'])
                ->name('admin.accounting.ledger')
                ->middleware('PermissionCheck:accounting_ledger_read');

            /** ------------- Balance Sheet ------------- **/
            Route::get('/balance-sheet', [BalanceSheetController::class, 'index'])
                ->name('admin.accounting.balance_sheet')
                ->middleware('PermissionCheck:accounting_bs_read');

            Route::get('/balance-sheet/print', [BalanceSheetController::class, 'print'])
                ->name('admin.accounting.balance_sheet.print')
                ->middleware('PermissionCheck:accounting_bs_export');

            Route::get('/balance-sheet/pdf', [BalanceSheetController::class, 'pdf'])
                ->name('admin.accounting.balance_sheet.pdf')
                ->middleware('PermissionCheck:accounting_bs_export');

            /** ------------- Account Groups ------------- **/
            Route::get('groups', [AccountGroupController::class, 'index'])
                ->name('admin.accounting.groups.index')
                ->middleware('PermissionCheck:accounting_group_read');

            Route::get('groups/create', [AccountGroupController::class, 'create'])
                ->name('admin.accounting.groups.create')
                ->middleware('PermissionCheck:accounting_group_create');

            Route::post('groups', [AccountGroupController::class, 'store'])
                ->name('admin.accounting.groups.store')
                ->middleware('PermissionCheck:accounting_group_create');

            Route::get('groups/{id}/edit', [AccountGroupController::class, 'edit'])
                ->name('admin.accounting.groups.edit')
                ->middleware('PermissionCheck:accounting_group_update');

            Route::put('groups/{id}', [AccountGroupController::class, 'update'])
                ->name('admin.accounting.groups.update')
                ->middleware('PermissionCheck:accounting_group_update');

            Route::delete('groups/{id}', [AccountGroupController::class, 'destroy'])
                ->name('admin.accounting.groups.destroy')
                ->middleware('PermissionCheck:accounting_group_delete');

            Route::get('groups/import', [AccountGroupController::class, 'importView'])
                ->name('admin.accounting.groups.import.view')
                ->middleware('PermissionCheck:accounting_group_import');

            Route::post('groups/import', [AccountGroupController::class, 'import'])
                ->name('admin.accounting.groups.import')
                ->middleware('PermissionCheck:accounting_group_import');

            Route::get('accounting/groups/sample-download', function () {
                $path = public_path('excel/account_group_import_sample.xlsx');
                return response()->download($path, 'account_group_import_sample.xlsx');
            })->name('admin.accounting.groups.sample.download')
              ->middleware('PermissionCheck:accounting_group_import');

            /** ------------- Profit & Loss ------------- **/
            Route::get('/profit-loss', [ProfitLossController::class, 'index'])
                ->name('admin.accounting.profit_loss')
                ->middleware('PermissionCheck:accounting_pl_read');

            Route::get('/profit-loss/monthly', [ProfitLossController::class, 'monthly'])
                ->name('admin.accounting.profit_loss.monthly')
                ->middleware('PermissionCheck:accounting_pl_read');

            Route::get('/profit-loss/print', [ProfitLossController::class, 'print'])
                ->name('admin.accounting.profit_loss.print')
                ->middleware('PermissionCheck:accounting_pl_export');

            Route::get('/profit-loss/pdf', [ProfitLossController::class, 'pdf'])
                ->name('admin.accounting.profit_loss.pdf')
                ->middleware('PermissionCheck:accounting_pl_export');

            /** ---------------- Banks ---------------- **/
            Route::get('bank-accounts', [BankAccountController::class, 'index'])
                ->name('admin.accounting.bank_accounts.index')
                ->middleware('PermissionCheck:accounting_bank_read');

            Route::post('bank-accounts', [BankAccountController::class, 'store'])
                ->name('admin.accounting.bank_accounts.store')
                ->middleware('PermissionCheck:accounting_bank_create');

            Route::delete('bank-accounts/{id}', [BankAccountController::class, 'destroy'])
                ->name('admin.accounting.bank_account.destroy')
                ->middleware('PermissionCheck:accounting_bank_delete');

            /** --------------- Transfers --------------- **/
            Route::get('/transfers', [BankTransferController::class, 'index'])
                ->name('admin.accounting.transfers.index')
                ->middleware('PermissionCheck:accounting_transfer_read');

            Route::post('/transfers', [BankTransferController::class, 'store'])
                ->name('admin.accounting.transfers.store')
                ->middleware('PermissionCheck:accounting_transfer_create');

            /** --------------- Customers --------------- **/
            Route::get('/customers', [CustomerController::class, 'index'])
                ->name('admin.accounting.customers.index')
                ->middleware('PermissionCheck:accounting_customer_read');

            Route::get('/customers/create', [CustomerController::class, 'create'])
                ->name('admin.accounting.customers.create')
                ->middleware('PermissionCheck:accounting_customer_create');

            Route::post('/customers', [CustomerController::class, 'store'])
                ->name('admin.accounting.customers.store')
                ->middleware('PermissionCheck:accounting_customer_create');

            Route::get('/customers/{id}/edit', [CustomerController::class, 'edit'])
                ->name('admin.accounting.customers.edit')
                ->middleware('PermissionCheck:accounting_customer_update');

            Route::put('/customers/{id}', [CustomerController::class, 'update'])
                ->name('admin.accounting.customers.update')
                ->middleware('PermissionCheck:accounting_customer_update');

            Route::delete('/customers/{id}', [CustomerController::class, 'destroy'])
                ->name('admin.accounting.customers.destroy')
                ->middleware('PermissionCheck:accounting_customer_delete');

            /** ---------------- Vendors ---------------- **/
            Route::get('/vendors', [VendorController::class, 'index'])
                ->name('admin.accounting.vendors.index')
                ->middleware('PermissionCheck:accounting_vendor_read');

            Route::get('/vendors/create', [VendorController::class, 'create'])
                ->name('admin.accounting.vendors.create')
                ->middleware('PermissionCheck:accounting_vendor_create');

            Route::post('/vendors', [VendorController::class, 'store'])
                ->name('admin.accounting.vendors.store')
                ->middleware('PermissionCheck:accounting_vendor_create');

            Route::get('/vendors/{id}/edit', [VendorController::class, 'edit'])
                ->name('admin.accounting.vendors.edit')
                ->middleware('PermissionCheck:accounting_vendor_update');

            Route::put('/vendors/{id}', [VendorController::class, 'update'])
                ->name('admin.accounting.vendors.update')
                ->middleware('PermissionCheck:accounting_vendor_update');

            Route::delete('/vendors/{id}', [VendorController::class, 'destroy'])
                ->name('admin.accounting.vendors.destroy')
                ->middleware('PermissionCheck:accounting_vendor_delete');

            /** ------------- Quick Expenses ------------- **/
            Route::get('/quick-expenses', [QuickExpensesController::class, 'index'])
                ->name('admin.accounting.quick_expenses.index')
                ->middleware('PermissionCheck:accounting_quick_expense_read');

            Route::get('/quick-expenses/create', [QuickExpensesController::class, 'create'])
                ->name('admin.accounting.quick_expenses.create')
                ->middleware('PermissionCheck:accounting_quick_expense_create');

            Route::post('/quick-expenses', [QuickExpensesController::class, 'store'])
                ->name('admin.accounting.quick_expenses.store')
                ->middleware('PermissionCheck:accounting_quick_expense_create');

            Route::get('/quick-expenses/{id}/edit', [QuickExpensesController::class, 'edit'])
                ->name('admin.accounting.quick_expenses.edit')
                ->middleware('PermissionCheck:accounting_quick_expense_update');

            Route::put('/quick-expenses/{id}', [QuickExpensesController::class, 'update'])
                ->name('admin.accounting.quick_expenses.update')
                ->middleware('PermissionCheck:accounting_quick_expense_update');

            Route::delete('/quick-expenses/{id}', [QuickExpensesController::class, 'destroy'])
                ->name('admin.accounting.quick_expenses.destroy')
                ->middleware('PermissionCheck:accounting_quick_expense_delete');

            /** ----------------- Bills ------------------ **/
            Route::get('/bills', [BillController::class,'index'])
                ->name('admin.accounting.bills.index')
                ->middleware('PermissionCheck:accounting_bill_read');

            Route::get('/bills/create', [BillController::class,'create'])
                ->name('admin.accounting.bills.create')
                ->middleware('PermissionCheck:accounting_bill_create');

            Route::post('/bills', [BillController::class,'store'])
                ->name('admin.accounting.bills.store')
                ->middleware('PermissionCheck:accounting_bill_create');

            Route::get('/bills/{bill}/edit', [BillController::class,'edit'])
                ->name('admin.accounting.bills.edit')
                ->middleware('PermissionCheck:accounting_bill_update');

            Route::put('/bills/{bill}', [BillController::class,'update'])
                ->name('admin.accounting.bills.update')
                ->middleware('PermissionCheck:accounting_bill_update');

            Route::delete('/bills/{bill}', [BillController::class,'destroy'])
                ->name('admin.accounting.bills.destroy')
                ->middleware('PermissionCheck:accounting_bill_delete');

            /** ------------ Bill Payments --------------- **/
            Route::get('/bill-payments', [BillPaymentController::class,'index'])
                ->name('admin.accounting.bill_payments.index')
                ->middleware('PermissionCheck:accounting_bill_payment_read');

            Route::get('/bills/{bill}/pay', [BillPaymentController::class,'create'])
                ->name('admin.accounting.bills.pay.create')
                ->middleware('PermissionCheck:accounting_bill_payment_create');

            Route::post('/bills/{bill}/pay', [BillPaymentController::class,'store'])
                ->name('admin.accounting.bills.pay.store')
                ->middleware('PermissionCheck:accounting_bill_payment_create');

            /** ------------- Trial Balance --------------- **/
            Route::get('/trial-balance', [TrialBalanceController::class, 'index'])
                ->name('admin.accounting.trial_balance')
                ->middleware('PermissionCheck:accounting_trial_balance_read');

            /** ----------------- Assets ----------------- **/
            Route::get('/assets', [AssetController::class,'index'])
                ->name('admin.accounting.assets.index')
                ->middleware('PermissionCheck:accounting_asset_read');

            Route::get('/assets/create', [AssetController::class,'create'])
                ->name('admin.accounting.assets.create')
                ->middleware('PermissionCheck:accounting_asset_create');

            Route::post('/assets', [AssetController::class,'store'])
                ->name('admin.accounting.assets.store')
                ->middleware('PermissionCheck:accounting_asset_create');

            Route::get('/assets/{asset}/edit', [AssetController::class,'edit'])
                ->name('admin.accounting.assets.edit')
                ->middleware('PermissionCheck:accounting_asset_update');

            Route::put('/assets/{asset}', [AssetController::class,'update'])
                ->name('admin.accounting.assets.update')
                ->middleware('PermissionCheck:accounting_asset_update');

            Route::delete('/assets/{asset}', [AssetController::class,'destroy'])
                ->name('admin.accounting.assets.destroy')
                ->middleware('PermissionCheck:accounting_asset_delete');

            // Depreciation (global month run)
            Route::post('/assets/depreciation/run', [AssetDepreciationController::class,'runForMonth'])
                ->name('admin.accounting.assets.depr.run')
                ->middleware('PermissionCheck:accounting_asset_depr_post');

            // Depreciation (one asset / month)
            Route::post('/assets/{asset}/depreciation', [AssetDepreciationController::class,'runForAsset'])
                ->name('admin.accounting.assets.depr.asset')
                ->middleware('PermissionCheck:accounting_asset_depr_post');
            
            // Audit                         
            Route::get('/audits', [AuditController::class, 'index'])
                ->name('admin.audits.index')
                ->middleware('PermissionCheck:audit_read');

            Route::get('/audits/{audit}', [AuditController::class, 'show'])
            ->name('admin.audits.show')
            ->middleware('PermissionCheck:audit_read');
        });
    });
});