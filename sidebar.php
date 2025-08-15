<!-- Accounting  -->
@if(addon_is_activated('accounting_addon') && (hasPermission('accounting_coa_read') ||
hasPermission('accounting_journal_read')))
<li class="nav-item dropdown @yield('accounting_active')">
    <a href="javascript:void(0)" class="nav-link has-dropdown" data-toggle="dropdown">
        <i class="bx bx-book"></i>
        <span>{{ __('Accounting') }}</span>
    </a>
    <ul class="dropdown-menu">
        @if(hasPermission('accounting_coa_read'))
        <li class="@yield('chart_of_accounts')">
            <a class="nav-link" href="{{ route('admin.accounting.coa') }}">
                <i class="bx bx-collection"></i> <span>{{ __('Chart of Accounts') }}</span>
            </a>
        </li>
        <li class="@yield('account_groups')">
            <a class="nav-link" href="{{ route('admin.accounting.groups.index') }}">
                <i class="bx bx-layer"></i> <span>{{ __('Account Groups') }}</span>
            </a>
        </li>
        @endif

        @if(hasPermission('accounting_journal_read'))
        <li class="@yield('journal_entries')">
            <a class="nav-link" href="{{ route('admin.accounting.journals') }}">
                <i class="bx bx-notepad"></i> <span>{{ __('Journal Entries') }}</span>
            </a>
        </li>
        @endif

        <li class="@yield('ledger_summary')">
            <a class="nav-link" href="{{ route('admin.accounting.ledger') }}">
                <i class="bx bx-spreadsheet"></i> <span>{{ __('Ledger Summary') }}</span>
            </a>
        </li>
        <li class="@yield('trial_balance')">
            <a class="nav-link" href="{{ route('admin.accounting.trial_balance') }}">
                <i class="bx bx-buoy"></i> <span>{{ __('Trial Balance') }}</span>
            </a>
        </li>

        <li class="@yield('balance_sheet')">
            <a class="nav-link" href="{{ route('admin.accounting.balance_sheet') }}">
                <i class="bx bx-line-chart"></i> <span>{{ __('Balance Sheet') }}</span>
            </a>
        </li>
        <li class="@yield('profit_loss')">
            <a class="nav-link" href="{{ route('admin.accounting.profit_loss') }}">
                <i class="bx bx-bar-chart-alt-2"></i> <span>{{ __('Profit & Loss') }}</span>
            </a>
        </li>
        <li class="@yield('bank_mng')">
            <a class="nav-link" href="{{ route('admin.accounting.bank_accounts.index') }}">
                <i class="bx bx-building"></i> <span>{{ __('Bank') }}</span>
            </a>
        </li>
        <li class="@yield('customers')">
            <a class="nav-link" href="{{ route('admin.accounting.customers.index') }}">
                <i class="bx bx-user"></i> <span>{{ __('Customers') }}</span>
            </a>
        </li>
        <li class="@yield('vendors')">
            <a class="nav-link" href="{{ route('admin.accounting.vendors.index') }}">
                <i class="bx bx-store"></i> <span>{{ __('Vendors') }}</span>
            </a>
        </li>
        <li class="@yield('quick_expenses')">
            <a class="nav-link" href="{{ route('admin.accounting.quick_expenses.index') }}">
                <i class="bx bx-wallet"></i> <span>{{ __('Quick Expenses') }}</span>
            </a>
        </li>
        <li class="@yield('bills')">
            <a class="nav-link" href="{{ route('admin.accounting.bills.index') }}">
                <i class="bx bx-file"></i> <span>{{ __('Bills') }}</span>
            </a>
        </li>
        <li class="@yield('bill_payments')">
            <a class="nav-link" href="{{ route('admin.accounting.bill_payments.index') }}">
                <i class="bx bx-credit-card"></i> <span>{{ __('Bill Payments') }}</span>
            </a>
        </li>
    </ul>
</li>
@endif