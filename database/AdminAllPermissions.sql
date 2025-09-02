-- =========================================
-- ADMIN — register perms + grant to admins
-- Requires MySQL 5.7+/MariaDB 10.2+ (JSON)
-- =========================================
START TRANSACTION;

-- ---------- A) REGISTER/UPDATE "accounting" in permissions ----------
SET @keywords = JSON_OBJECT(
  'access','accounting_access',

  -- Chart of Accounts
  'coa_read','accounting_coa_read',
  'coa_create','accounting_coa_create',
  'coa_update','accounting_coa_update',
  'coa_delete','accounting_coa_delete',
  'coa_import','accounting_coa_import',

  -- Account Groups
  'group_read','accounting_group_read',
  'group_create','accounting_group_create',
  'group_update','accounting_group_update',
  'group_delete','accounting_group_delete',
  'group_import','accounting_group_import',

  -- Journals
  'journal_read','accounting_journal_read',
  'journal_create','accounting_journal_create',
  'journal_update','accounting_journal_update',
  'journal_delete','accounting_journal_delete',

  -- Reports
  'ledger_read','accounting_ledger_read',
  'trial_balance_read','accounting_trial_balance_read',
  'bs_read','accounting_bs_read',
  'bs_export','accounting_bs_export',
  'pl_read','accounting_pl_read',
  'pl_export','accounting_pl_export',

  -- Banking
  'bank_read','accounting_bank_read',
  'bank_create','accounting_bank_create',
  'bank_delete','accounting_bank_delete',

  -- Transfers
  'transfer_read','accounting_transfer_read',
  'transfer_create','accounting_transfer_create',
  'transfer_delete','accounting_transfer_delete',

  -- Contacts
  'customer_read','accounting_customer_read',
  'customer_create','accounting_customer_create',
  'customer_update','accounting_customer_update',
  'customer_delete','accounting_customer_delete',

  'vendor_read','accounting_vendor_read',
  'vendor_create','accounting_vendor_create',
  'vendor_update','accounting_vendor_update',
  'vendor_delete','accounting_vendor_delete',

  -- Quick Expenses
  'quick_expense_read','accounting_quick_expense_read',
  'quick_expense_create','accounting_quick_expense_create',
  'quick_expense_update','accounting_quick_expense_update',
  'quick_expense_delete','accounting_quick_expense_delete',

  -- Bills & Payments
  'bill_read','accounting_bill_read',
  'bill_create','accounting_bill_create',
  'bill_update','accounting_bill_update',
  'bill_delete','accounting_bill_delete',

  'bill_payment_read','accounting_bill_payment_read',
  'bill_payment_create','accounting_bill_payment_create',

  -- Assets & Depreciation
  'asset_read','accounting_asset_read',
  'asset_create','accounting_asset_create',
  'asset_update','accounting_asset_update',
  'asset_delete','accounting_asset_delete',
  'asset_depr_post','accounting_asset_depr_post'
);

DELETE FROM `permissions` WHERE `attribute` = 'accounting';
INSERT INTO `permissions` (`attribute`,`keywords`,`created_at`,`updated_at`)
VALUES ('accounting', @keywords, NOW(), NOW());

-- ---------- (optional) REGISTER/UPDATE "audit" ----------
SET @audit = JSON_OBJECT(
  'read','audit_read',
  'view','audit_view'
);

DELETE FROM `permissions` WHERE `attribute`='audit';
INSERT INTO `permissions` (`attribute`,`keywords`,`created_at`,`updated_at`)
VALUES ('audit', @audit, NOW(), NOW());

-- ---------- B) GRANT to all ADMIN users ----------
-- Parent gate
SET @p='accounting_access';
UPDATE `users`
SET `permissions` = IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'), JSON_QUOTE(@p), '$'),
                       `permissions`,
                       JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()), '$', @p))
WHERE user_type='admin';

-- Chart of Accounts
SET @p='accounting_coa_read';   UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';
SET @p='accounting_coa_create'; UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';
SET @p='accounting_coa_update'; UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';
SET @p='accounting_coa_delete'; UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';
SET @p='accounting_coa_import'; UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';

-- Account Groups
SET @p='accounting_group_read';   UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';
SET @p='accounting_group_create'; UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';
SET @p='accounting_group_update'; UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';
SET @p='accounting_group_delete'; UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';
SET @p='accounting_group_import'; UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';

-- Journals
SET @p='accounting_journal_read';   UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';
SET @p='accounting_journal_create'; UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';
SET @p='accounting_journal_update'; UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';
SET @p='accounting_journal_delete'; UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';

-- Reports
SET @p='accounting_ledger_read';        UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';
SET @p='accounting_trial_balance_read'; UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';
SET @p='accounting_bs_read';            UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';
SET @p='accounting_bs_export';          UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';
SET @p='accounting_pl_read';            UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';
SET @p='accounting_pl_export';          UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';

-- Banking
SET @p='accounting_bank_read';   UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';
SET @p='accounting_bank_create'; UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';
SET @p='accounting_bank_delete'; UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';

-- Transfers
SET @p='accounting_transfer_read';   UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';
SET @p='accounting_transfer_create'; UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';
SET @p='accounting_transfer_delete'; UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';

-- Contacts (Customers)
SET @p='accounting_customer_read';   UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';
SET @p='accounting_customer_create'; UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';
SET @p='accounting_customer_update'; UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';
SET @p='accounting_customer_delete'; UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';

-- Contacts (Vendors)
SET @p='accounting_vendor_read';   UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';
SET @p='accounting_vendor_create'; UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';
SET @p='accounting_vendor_update'; UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';
SET @p='accounting_vendor_delete'; UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';

-- Quick Expenses
SET @p='accounting_quick_expense_read';   UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';
SET @p='accounting_quick_expense_create'; UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';
SET @p='accounting_quick_expense_update'; UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';
SET @p='accounting_quick_expense_delete'; UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';

-- Bills & Payments
SET @p='accounting_bill_read';            UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';
SET @p='accounting_bill_create';          UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';
SET @p='accounting_bill_update';          UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';
SET @p='accounting_bill_delete';          UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';

SET @p='accounting_bill_payment_read';    UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';
SET @p='accounting_bill_payment_create';  UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';

-- Assets
SET @p='accounting_asset_read';      UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';
SET @p='accounting_asset_create';    UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';
SET @p='accounting_asset_update';    UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';
SET @p='accounting_asset_delete';    UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';
SET @p='accounting_asset_depr_post'; UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';

-- Audits
SET @p='audit_read'; UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';
SET @p='audit_view'; UPDATE `users` SET `permissions`=IF(JSON_CONTAINS(COALESCE(`permissions`,'[]'),JSON_QUOTE(@p),'$'),`permissions`,JSON_ARRAY_APPEND(COALESCE(`permissions`,JSON_ARRAY()),'$',@p)) WHERE user_type='admin';

COMMIT;
