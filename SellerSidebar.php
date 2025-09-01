{{-- resources/views/seller/partials/sidebar.blade.php --}}
<li class="nav-item dropdown @yield('accounting_active')">
    <a href="javascript:void(0)" class="nav-link has-dropdown" data-toggle="dropdown">
        <i class="bx bx-calculator"></i><span>{{ __('Accounting') }}</span>
    </a>
    <ul class="dropdown-menu">
        <li class="@yield('accounting_groups')">
            <a class="nav-link" href="{{ route('seller.accounting.groups.index') }}">{{ __('Account Groups') }}</a>
        </li>
        <li class="@yield('accounting_coa')">
            <a class="nav-link" href="{{ route('seller.accounting.coa.index') }}">{{ __('Chart of Accounts') }}</a>
        </li>
        <li class="@yield('accounting_audit')">
            <a class="nav-link" href="{{ route('seller.audits.index') }}">{{ __('Audit Logs') }}</a>
        </li>
        <li class="@yield('accounting_journals')">
            <a class="nav-link" href="{{ route('seller.accounting.journals') }}">
                {{ __('Journal Entries') }}
            </a>
        </li>
    </ul>
</li>