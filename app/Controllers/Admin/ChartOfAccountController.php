<?php

namespace App\Http\Controllers\Admin\Addons;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ChartOfAccountController extends Controller
{
    public function index()
    {
        // Cache accounts list for 24 hours (1440 minutes)
        $accounts = Cache::remember('accounting.accounts', 1440, function () {
            return Account::orderBy('name')->get();
        });

        return view('addons.accounting.chart_of_accounts', compact('accounts'));
    }

    public function create()
    {
        return view('addons.accounting.account_form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,liability,equity,revenue,expense',
        ]);

        Account::create($request->only(['name', 'type', 'code', 'is_active']));

        // Invalidate cache after insert
        Cache::forget('accounting.accounts');

        return redirect()->route('admin.accounting.coa')->with('success', 'Account created successfully.');
    }
}
