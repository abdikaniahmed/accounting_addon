<!-- resources\views\admin\partials\sidebar.blade.php -->

<!-- Accounting  -->
@if(addon_is_activated('accounting_addon') && (hasPermission('accounting_coa_read') || hasPermission('accounting_journal_read')))
    <li class="nav-item dropdown @yield('accounting_active')">
        <a href="javascript:void(0)" class="nav-link has-dropdown" data-toggle="dropdown">
            <i class="bx bx-book"></i>
            <span>{{ __('Accounting') }}</span>
        </a>
        <ul class="dropdown-menu">
            @if(hasPermission('accounting_coa_read'))
                <li class="@yield('chart_of_accounts')">
                    <a class="nav-link" href="{{ route('admin.accounting.coa') }}">{{ __('Chart of Accounts') }}</a>
                </li>
                <li class="@yield('account_groups')">
                    <a class="nav-link" href="{{ route('admin.accounting.groups.index') }}">{{ __('Account Groups') }}</a>
                </li>
            @endif

            @if(hasPermission('accounting_journal_read'))
                <li class="@yield('journal_entries')">
                    <a class="nav-link" href="{{ route('admin.accounting.journals') }}">{{ __('Journal Entries') }}</a>
                </li>
            @endif

            <li class="@yield('ledger_summary')">
                <a class="nav-link" href="{{ route('admin.accounting.ledger') }}">{{ __('Ledger Summary') }}</a>
            </li>
            <li class="@yield('balance_sheet')">
                <a class="nav-link" href="{{ route('admin.accounting.balance_sheet') }}">
                    <i class="bx bx-line-chart"></i>
                    <span>{{ __('Balance Sheet') }}</span>
                </a>
            </li>
            <li class="@yield('profit_loss')">
                <a class="nav-link" href="{{ route('admin.accounting.profit_loss') }}">
                    <i class="bx bx-bar-chart-alt-2"></i>
                    <span>{{ __('Profit & Loss') }}</span>
                </a>
            </li>
        </ul>
    </li>
@endif
