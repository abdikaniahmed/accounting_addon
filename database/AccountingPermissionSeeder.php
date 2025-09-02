<?php

namespace Database\Seeders\Admin\Addon;
//News update --> 

use Illuminate\Database\Seeder;
use App\Models\Permission;

class AccountingPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $attributes = [
            'accounting' => [
                'access'               => 'accounting_access',
                'coa_read'             => 'accounting_coa_read',
                'coa_create'           => 'accounting_coa_create',
                'coa_update'           => 'accounting_coa_update',
                'coa_delete'           => 'accounting_coa_delete',
                'coa_import'           => 'accounting_coa_import',
                'group_read'           => 'accounting_group_read',
                'group_create'         => 'accounting_group_create',
                'group_update'         => 'accounting_group_update',
                'group_delete'         => 'accounting_group_delete',
                'group_import'         => 'accounting_group_import',
                'journal_read'         => 'accounting_journal_read',
                'journal_create'       => 'accounting_journal_create',
                'journal_update'       => 'accounting_journal_update',
                'journal_delete'       => 'accounting_journal_delete',
                'ledger_read'          => 'accounting_ledger_read',
                'trial_balance_read'   => 'accounting_trial_balance_read',
                'bs_read'              => 'accounting_bs_read',
                'bs_export'            => 'accounting_bs_export',
                'pl_read'              => 'accounting_pl_read',
                'pl_export'            => 'accounting_pl_export',
                'bank_read'            => 'accounting_bank_read',
                'bank_create'          => 'accounting_bank_create',
                'bank_delete'          => 'accounting_bank_delete',
                'transfer_read'        => 'accounting_transfer_read',
                'transfer_create'      => 'accounting_transfer_create',
                'transfer_delete'      => 'accounting_transfer_delete',
                'customer_read'        => 'accounting_customer_read',
                'customer_create'      => 'accounting_customer_create',
                'customer_update'      => 'accounting_customer_update',
                'customer_delete'      => 'accounting_customer_delete',
                'vendor_read'          => 'accounting_vendor_read',
                'vendor_create'        => 'accounting_vendor_create',
                'vendor_update'        => 'accounting_vendor_update',
                'vendor_delete'        => 'accounting_vendor_delete',
                'quick_expense_read'   => 'accounting_quick_expense_read',
                'quick_expense_create' => 'accounting_quick_expense_create',
                'quick_expense_update' => 'accounting_quick_expense_update',
                'quick_expense_delete' => 'accounting_quick_expense_delete',
                'bill_read'            => 'accounting_bill_read',
                'bill_create'          => 'accounting_bill_create',
                'bill_update'          => 'accounting_bill_update',
                'bill_delete'          => 'accounting_bill_delete',
                'bill_payment_read'    => 'accounting_bill_payment_read',
                'bill_payment_create'  => 'accounting_bill_payment_create',
                'asset_read'           => 'accounting_asset_read',
                'asset_create'         => 'accounting_asset_create',
                'asset_update'         => 'accounting_asset_update',
                'asset_delete'         => 'accounting_asset_delete',
                'asset_depr_post'      => 'accounting_asset_depr_post',

            ],
            'Auding' => [
                'audit_view'      => 'audit_read',
                'audit_show_detail'  => 'audit_read',
            ],
        ];

        foreach ($attributes as $key => $attribute) {
            $permission = Permission::firstOrNew(['attribute' => $key]);
            $current    = (array) $permission->keywords;
            $merged     = $current + $attribute;    // keep existing keys, add missing ones
            ksort($merged);
            $permission->attribute = $key;
            $permission->keywords  = $merged;
            $permission->save();
        }
    }
}