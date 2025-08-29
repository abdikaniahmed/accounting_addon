<?php
// routes/accounting-seller.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Seller\Addons\AccountGroupController as SellerAccountGroupController;
use App\Http\Controllers\Seller\Addons\SellerAuditController as SellerAuditController;

Route::middleware(['XSS','isInstalled'])->group(function () {
    Route::group([
        'prefix' => LaravelLocalization::setLocale(),
        'middleware' => ['localeSessionRedirect', 'localizationRedirect', 'localeViewPath', 'isInstalled']
    ], function () {

        // You can add your existing seller/login middleware here as you do elsewhere
        Route::middleware(['sellerCheck','loginCheck'])
            ->prefix('seller/accounting')
            ->group(function () {

            /** ------------- Account Groups (Seller) ------------- **/
            Route::get('groups', [SellerAccountGroupController::class, 'index'])
                ->name('seller.accounting.groups.index');

            Route::get('groups/create', [SellerAccountGroupController::class, 'create'])
                ->name('seller.accounting.groups.create');

            Route::post('groups', [SellerAccountGroupController::class, 'store'])
                ->name('seller.accounting.groups.store');

            Route::get('groups/{id}/edit', [SellerAccountGroupController::class, 'edit'])
                ->name('seller.accounting.groups.edit');

            Route::put('groups/{id}', [SellerAccountGroupController::class, 'update'])
                ->name('seller.accounting.groups.update');

            Route::delete('groups/{id}', [SellerAccountGroupController::class, 'destroy'])
                ->name('seller.accounting.groups.destroy');

            Route::get('groups/import', [SellerAccountGroupController::class, 'importView'])
                ->name('seller.accounting.groups.import.view');

            Route::post('groups/import', [SellerAccountGroupController::class, 'import'])
                ->name('seller.accounting.groups.import');

            Route::get('groups/sample-download', function () {
                $path = public_path('excel/account_group_import_sample.xlsx');
                return response()->download($path, 'account_group_import_sample.xlsx');
            })->name('seller.accounting.groups.sample.download');


            //Audit

            Route::get('/audits', [SellerAuditController::class, 'index'])->name('seller.audits.index');
        Route::get('/audits/{audit}', [SellerAuditController::class, 'show'])->name('seller.audits.show');
        });
    });
});