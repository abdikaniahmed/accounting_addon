<?php

namespace App\Http\Controllers\Admin\Addons;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Accounting\Account;
use App\Models\Accounting\AccountGroup;

class ChartOfAccountController extends Controller
{
    public function index()
    {
        $accounts = Cache::remember('accounting.accounts', 1440, function () {
            return Account::orderBy('name')->get();
        });

        return view('addons.accounting.chart_of_accounts', compact('accounts'));
    }

    public function create()
    {
        $groups = AccountGroup::orderBy('name')->get();
        return view('addons.accounting.account_form', compact('groups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,liability,equity,revenue,expense',
            'account_group_id' => 'nullable|exists:acc_account_groups,id',
        ]);

        Account::create([
            'name' => $request->name,
            'type' => $request->type,
            'code' => $request->code,
            'is_active' => $request->has('is_active'),
            'account_group_id' => $request->account_group_id,
        ]);

        Cache::forget('accounting.accounts');

        return redirect()->route('admin.accounting.coa')->with('success', 'Account created successfully.');
    }

    public function edit($id)
    {
        $account = Account::findOrFail($id);
        $groups = AccountGroup::orderBy('name')->get();

        return view('addons.accounting.account_form', compact('account', 'groups'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,liability,equity,revenue,expense',
            'account_group_id' => 'nullable|exists:acc_account_groups,id',
        ]);

        $account = Account::findOrFail($id);
        $account->update([
            'name' => $request->name,
            'type' => $request->type,
            'code' => $request->code,
            'is_active' => $request->has('is_active'),
            'account_group_id' => $request->account_group_id,
        ]);

        Cache::forget('accounting.accounts');

        return redirect()->route('admin.accounting.coa')->with('success', 'Account updated successfully.');
    }

    public function destroy($id)
    {
        $account = Account::findOrFail($id);
        $account->delete();

        Cache::forget('accounting.accounts');

        return response()->json(['status' => 'success', 'message' => 'Account deleted successfully.']);
    }

    public function importView()
    {
        return view('addons.accounting.chart_of_accounts_import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $collection = Excel::toCollection(null, $request->file('file'));
        $rows = $collection[0];
        $imported = 0;

        foreach ($rows->skip(1) as $row) {
            $name   = trim($row[0]);
            $type   = strtolower(trim($row[1]));
            $code   = trim($row[2]);
            $group  = trim($row[3]);
            $active = strtolower(trim($row[4])) === 'yes';

            if (empty($name) || !in_array($type, ['asset', 'liability', 'equity', 'revenue', 'expense'])) {
                continue;
            }

            $group_id = null;
            if (!empty($group)) {
                $groupModel = AccountGroup::firstOrCreate(['name' => $group]);
                $group_id = $groupModel->id;
            }

            if (!Account::where('name', $name)->exists()) {
                Account::create([
                    'name' => $name,
                    'type' => $type,
                    'code' => $code,
                    'is_active' => $active,
                    'account_group_id' => $group_id,
                ]);
                $imported++;
            }
        }

        Cache::forget('accounting.accounts');

        return redirect()->route('admin.accounting.coa')
            ->with('success', "$imported accounts imported successfully.");
    }
}
