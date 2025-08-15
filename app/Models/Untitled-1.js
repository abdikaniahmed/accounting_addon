
 Directors --> 

app\Controllers\Admin\BillController.php
app\Controllers\Admin\BillPaymentController.php

app/Models/Bill.php
app\Models\BillItem.php

View\Admin\bill_pay.blade.php
View\Admin\bills_form.blade.php
View\Admin\bills_index.blade.php


Please add in Config.json  --> put same style as before Directors place Controllers, Place of Models, and Place of Views



then add update Config.json --> {
  "name": "Accounting System",
  "addon_identifier": "accounting_addon",
  "version": "1.4.0",
  "required_cms_version": "192",
  "addon_banner": "images/addons/accounting.png",

  "directories": [
    "public/addons",
    "public/images/addons",
    "app/Http/Controllers/Admin/Addons",
    "app/Repositories/Admin/Addon",
    "app/Repositories/Interfaces/Admin/Addon",
    "app/Models/Accounting",
    "resources/views/addons/accounting",
    "routes"
  ],

  "sql_files": [
    "database/install.sql"
  ],

  "files": [
    {
      "from_directory": "images/accounting.png",
      "to_directory": "public/images/addons/accounting.png"
    },

    {
      "from_directory": "app/Controllers/Admin/ChartOfAccountController.php",
      "to_directory": "app/Http/Controllers/Admin/Addons/ChartOfAccountController.php"
    },
    {
      "from_directory": "app/Controllers/Admin/AccountGroupController.php",
      "to_directory": "app/Http/Controllers/Admin/Addons/AccountGroupController.php"
    },
    {
      "from_directory": "app/Controllers/Admin/JournalEntryController.php",
      "to_directory": "app/Http/Controllers/Admin/Addons/JournalEntryController.php"
    },
    {
      "from_directory": "app/Controllers/Admin/LedgerController.php",
      "to_directory": "app/Http/Controllers/Admin/Addons/LedgerController.php"
    },
    {
      "from_directory": "app/Controllers/Admin/BalanceSheetController.php",
      "to_directory": "app/Http/Controllers/Admin/Addons/BalanceSheetController.php"
    },
    {
      "from_directory": "app/Controllers/Admin/ProfitLossController.php",
      "to_directory": "app/Http/Controllers/Admin/Addons/ProfitLossController.php"
    },
    {
      "from_directory": "app/Controllers/Admin/BankAccountController.php",
      "to_directory": "app/Http/Controllers/Admin/Addons/BankAccountController.php"
    },
    {
      "from_directory": "app/Controllers/Admin/BankTransferController.php",
      "to_directory": "app/Http/Controllers/Admin/Addons/BankTransferController.php"
    },
    {
      "from_directory": "app/Controllers/Admin/CustomerController.php",
      "to_directory": "app/Http/Controllers/Admin/Addons/CustomerController.php"
    },
    {
      "from_directory": "app/Controllers/Admin/VendorController.php",
      "to_directory": "app/Http/Controllers/Admin/Addons/VendorController.php"
    },
    {
      "from_directory": "app/Controllers/Admin/QuickExpensesController.php",
      "to_directory": "app/Http/Controllers/Admin/Addons/QuickExpensesController.php"
    },


    {
      "from_directory": "app/Repositories/AccountingRepository.php",
      "to_directory": "app/Repositories/Admin/Addon/AccountingRepository.php"
    },
    {
      "from_directory": "app/Repositories/AccountingInterface.php",
      "to_directory": "app/Repositories/Interfaces/Admin/Addon/AccountingInterface.php"
    },
 {
      "from_directory": "app/Services/JournalService.php",
      "to_directory": "app/Services/JournalService.php"
    },

    {
      "from_directory": "app/Models/Account.php",
      "to_directory": "app/Models/Accounting/Account.php"
    },
    {
      "from_directory": "app/Models/AccountGroup.php",
      "to_directory": "app/Models/Accounting/AccountGroup.php"
    },


    {
      "from_directory": "app/Models/JournalEntry.php",
      "to_directory": "app/Models/Accounting/JournalEntry.php"
    },
    {
      "from_directory": "app/Models/JournalItem.php",
      "to_directory": "app/Models/Accounting/JournalItem.php"
    },
    {
      "from_directory": "app/Models/Contact.php",
      "to_directory": "app/Models/Accounting/Contact.php"
    },
    {
      "from_directory": "app/Models/QuickExpense.php",
      "to_directory": "app/Models/Accounting/QuickExpense.php"
    },

 
    {
      "from_directory": "View/Admin/journal_form.blade.php",
      "to_directory": "resources/views/addons/accounting/journal_form.blade.php"
    },  
    {
      "from_directory": "View/Admin/journal_show.blade.php",
      "to_directory": "resources/views/addons/accounting/journal_show.blade.php"
    },
    {
      "from_directory": "View/Admin/chart_of_accounts.blade.php",
      "to_directory": "resources/views/addons/accounting/chart_of_accounts.blade.php"
    },
    {
      "from_directory": "View/Admin/account_form.blade.php",
      "to_directory": "resources/views/addons/accounting/account_form.blade.php"
    },
     {
      "from_directory": "View/Admin/ledger_summary.blade.php",
      "to_directory": "resources/views/addons/accounting/ledger_summary.blade.php"
    },
    {
      "from_directory": "View/Admin/balance_sheet.blade.php",
      "to_directory": "resources/views/addons/accounting/balance_sheet.blade.php"
    },
    {
      "from_directory": "View/Admin/account_groups_create.blade.php",
      "to_directory": "resources/views/addons/accounting/account_groups_create.blade.php"
    }, 
    {
      "from_directory": "View/Admin/account_groups_edit.blade.php",
      "to_directory": "resources/views/addons/accounting/account_groups_edit.blade.php"
    },
    {
      "from_directory": "View/Admin/account_groups_index.blade.php",
      "to_directory": "resources/views/addons/accounting/account_groups_index.blade.php"
    }, 
     {
      "from_directory": "View/Admin/account_groups_import.blade.php",
      "to_directory": "resources/views/addons/accounting/account_groups_import.blade.php"
    }, 
    {
      "from_directory": "View/Admin/chart_of_accounts_import.blade.php",
      "to_directory": "resources/views/addons/accounting/chart_of_accounts_import.blade.php"
    }, 
    {
      "from_directory": "View/Admin/balance_sheet_print.blade.php",
      "to_directory": "resources/views/addons/accounting/balance_sheet_print.blade.php"
    }, 
    {
      "from_directory": "View/Admin/balance_sheet_pdf.blade.php",
      "to_directory": "resources/views/addons/accounting/balance_sheet_pdf.blade.php"
    }, 
    {
      "from_directory": "View/Admin/profit_loss.blade.php",
      "to_directory": "resources/views/addons/accounting/profit_loss.blade.php"
    }, 
    {
      "from_directory": "View/Admin/profit_loss_monthly.blade.php",
      "to_directory": "resources/views/addons/accounting/profit_loss_monthly.blade.php"
    }, 
    {
      "from_directory": "View/Admin/profit_loss_pdf.blade.php",
      "to_directory": "resources/views/addons/accounting/profit_loss_pdf.blade.php"
    }, 
    {
      "from_directory": "View/Admin/profit_loss_print.blade.php",
      "to_directory": "resources/views/addons/accounting/profit_loss_print.blade.php"
    }, 
    {
      "from_directory": "View/Admin/bank_mng.blade.php",
      "to_directory": "resources/views/addons/accounting/bank_mng.blade.php"
    }, 
    {
      "from_directory": "View/Admin/bank_transfer.blade.php",
      "to_directory": "resources/views/addons/accounting/bank_transfer.blade.php"
    },
    {
      "from_directory": "View/Admin/customers_create.blade.php",
      "to_directory": "resources/views/addons/accounting/customers_create.blade.php"
    }, 
    {
      "from_directory": "View/Admin/customers_index.blade.php",
      "to_directory": "resources/views/addons/accounting/customers_index.blade.php"
    },  
    {
      "from_directory": "View/Admin/customers_edit.blade.php",
      "to_directory": "resources/views/addons/accounting/customers_edit.blade.php"
    },  
    {
      "from_directory": "View/Admin/vendors_index.blade.php",
      "to_directory": "resources/views/addons/accounting/vendors_index.blade.php"
    },  
    {
      "from_directory": "View/Admin/vendors_create.blade.php",
      "to_directory": "resources/views/addons/accounting/vendors_create.blade.php"
    },  
    {
      "from_directory": "View/Admin/vendors_edit.blade.php",
      "to_directory": "resources/views/addons/accounting/vendors_edit.blade.php"
    },  
      {
      "from_directory": "View/Admin/quick_expenses_form.blade.php",
      "to_directory": "resources/views/addons/accounting/quick_expenses_form.blade.php"
    },  
    {
      "from_directory": "View/Admin/quick_expenses_index.blade.php",
      "to_directory": "resources/views/addons/accounting/quick_expenses_index.blade.php"
    },  

    {
      "from_directory": "routes/accounting-system.php",
      "to_directory": "routes/accounting-system.php"
    },
    {
      "from_directory": "files/account_group_import_sample.xlsx",
      "to_directory": "public/excel/account_group_import_sample.xlsx"
    },
    {
      "from_directory": "files/chart_of_accounts_import_sample.xlsx",
      "to_directory": "public/excel/chart_of_accounts_import_sample.xlsx"
    }

  ]
}
