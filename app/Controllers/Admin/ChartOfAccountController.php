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
            // (Optional) order money accounts first, then by name
            return Account::orderByDesc('is_money')->orderBy('name')->get();
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
            'name'             => 'required|string|max:255',
            'type'             => 'required|in:asset,liability,equity,revenue,expense',
            'account_group_id' => 'nullable|exists:acc_account_groups,id',
            'is_money'         => 'sometimes|boolean',
        ]);

        Account::create([
            'name'             => $request->name,
            'type'             => $request->type,
            'code'             => $request->code,
            'is_active'        => $request->has('is_active'),
            'account_group_id' => $request->account_group_id,
            'is_money'         => $request->boolean('is_money'),
        ]);

        Cache::forget('accounting.accounts');

        return redirect()->route('admin.accounting.coa')
            ->with('success', __('Account created successfully.'));
    }

    public function edit($id)
    {
        $account = Account::findOrFail($id);
        $groups  = AccountGroup::orderBy('name')->get();

        return view('addons.accounting.account_form', compact('account', 'groups'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name'             => 'required|string|max:255',
            'type'             => 'required|in:asset,liability,equity,revenue,expense',
            'account_group_id' => 'nullable|exists:acc_account_groups,id',
            'is_money'         => 'sometimes|boolean',
        ]);

        $account = Account::findOrFail($id);
        $account->update([
            'name'             => $request->name,
            'type'             => $request->type,
            'code'             => $request->code,
            'is_active'        => $request->has('is_active'),
            'account_group_id' => $request->account_group_id,
            'is_money'         => $request->boolean('is_money'),
        ]);

        Cache::forget('accounting.accounts');

        return redirect()->route('admin.accounting.coa')
            ->with('success', __('Account updated successfully.'));
    }

    public function destroy($id)
    {
        $account = Account::findOrFail($id);
        $account->delete();

        Cache::forget('accounting.accounts');

        return response()->json(['status' => 'success', 'message' => __('Account deleted successfully.')]);
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
        $rows      = $collection[0] ?? collect();
        $imported  = 0;

        foreach ($rows->skip(1) as $row) {
            $name    = trim((string)($row[0] ?? ''));
            $type    = strtolower(trim((string)($row[1] ?? '')));
            $code    = trim((string)($row[2] ?? ''));
            $group   = trim((string)($row[3] ?? ''));
            $active  = $this->parseBool($row[4] ?? null);
            $isMoney = $this->parseBool($row[5] ?? null); // NEW: 6th column

            if (empty($name) || !in_array($type, ['asset', 'liability', 'equity', 'revenue', 'expense'])) {
                continue;
            }

            $group_id = null;
            if (!empty($group)) {
                $groupModel = AccountGroup::firstOrCreate(['name' => $group]);
                $group_id   = $groupModel->id;
            }

            if (!Account::where('name', $name)->exists()) {
                Account::create([
                    'name'             => $name,
                    'type'             => $type,
                    'code'             => $code,
                    'is_active'        => $active,
                    'account_group_id' => $group_id,
                    'is_money'         => $isMoney,
                ]);
                $imported++;
            }
        }

        Cache::forget('accounting.accounts');

        return redirect()->route('admin.accounting.coa')
            ->with('success', __("$imported accounts imported successfully."));
    }

    /** Parse common boolean-ish values (yes/no/true/false/1/0/y/n). */
    private function parseBool($val): bool
    {
        $v = strtolower(trim((string)$val));
        return in_array($v, ['1','true','yes','y'], true);
    }
}