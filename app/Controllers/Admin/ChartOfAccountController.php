<?php

namespace App\Http\Controllers\Admin\Addons;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Account;
use App\Models\Accounting\AccountGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;

class ChartOfAccountController extends Controller
{
    /** List ONLY global accounts (admin space) */
    public function index()
    {
        $accounts = Cache::remember('accounting.admin.accounts', 1440, function () {
            return Account::onlyGlobal()
                ->orderByDesc('is_money')
                ->orderBy('name')
                ->get();
        });

        return view('addons.accounting.chart_of_accounts', compact('accounts'));
    }

    /** Create form (global groups only) */
    public function create()
    {
        $groups = AccountGroup::onlyGlobal()->orderBy('name')->get();
        return view('addons.accounting.account_form', compact('groups'));
    }

    /** Store a GLOBAL account (seller_id stays NULL) */
    public function store(Request $request)
    {
        $request->validate([
            'name'             => 'required|string|max:255',
            'type'             => 'required|in:asset,liability,equity,revenue,expense',
            'account_group_id' => 'nullable|exists:acc_account_groups,id',
            'is_money'         => 'sometimes|boolean',
        ]);

        // Guard: ensure selected group is global as well
        $groupId = $request->account_group_id;
        if ($groupId) {
            AccountGroup::onlyGlobal()->findOrFail($groupId);
        }

        Account::create([
            'name'             => $request->name,
            'type'             => $request->type,
            'code'             => $request->code,
            'is_active'        => $request->has('is_active'),
            'account_group_id' => $groupId,
            'is_money'         => $request->boolean('is_money'),
            // seller_id intentionally NULL → global
        ]);

        Cache::forget('accounting.admin.accounts');

        return redirect()->route('admin.accounting.coa')
            ->with('success', __('Account created successfully.'));
    }

    /** Edit GLOBAL account only */
    public function edit($id)
    {
        $account = Account::onlyGlobal()->findOrFail($id);
        $groups  = AccountGroup::onlyGlobal()->orderBy('name')->get();

        return view('addons.accounting.account_form', compact('account', 'groups'));
    }

    /** Update GLOBAL account only */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name'             => 'required|string|max:255',
            'type'             => 'required|in:asset,liability,equity,revenue,expense',
            'account_group_id' => 'nullable|exists:acc_account_groups,id',
            'is_money'         => 'sometimes|boolean',
        ]);

        $account = Account::onlyGlobal()->findOrFail($id);

        // Guard: group must be global
        $groupId = $request->account_group_id;
        if ($groupId) {
            AccountGroup::onlyGlobal()->findOrFail($groupId);
        }

        $account->update([
            'name'             => $request->name,
            'type'             => $request->type,
            'code'             => $request->code,
            'is_active'        => $request->has('is_active'),
            'account_group_id' => $groupId,
            'is_money'         => $request->boolean('is_money'),
        ]);

        Cache::forget('accounting.admin.accounts');

        return redirect()->route('admin.accounting.coa')
            ->with('success', __('Account updated successfully.'));
    }

    /** Delete GLOBAL account only */
    public function destroy($id)
    {
        $account = Account::onlyGlobal()->findOrFail($id);
        $account->delete();

        Cache::forget('accounting.admin.accounts');

        return response()->json(['status' => 'success', 'message' => __('Account deleted successfully.')]);
    }

    public function importView()
    {
        return view('addons.accounting.chart_of_accounts_import');
    }

    /** Import as GLOBAL (seller_id = NULL) */
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
            $isMoney = $this->parseBool($row[5] ?? null);

            if (empty($name) || !in_array($type, ['asset','liability','equity','revenue','expense'], true)) {
                continue;
            }

            // Ensure/resolve GLOBAL group
            $groupId = null;
            if ($group !== '') {
                $groupModel = AccountGroup::onlyGlobal()->where('name', $group)->first();
                if (!$groupModel) {
                    $groupModel = AccountGroup::create(['name' => $group]); // seller_id = NULL by default
                }
                $groupId = $groupModel->id;
            }

            // Avoid duplicate global names
            $exists = Account::onlyGlobal()->where('name', $name)->exists();
            if ($exists) continue;

            Account::create([
                'name'             => $name,
                'type'             => $type,
                'code'             => $code,
                'is_active'        => $active,
                'account_group_id' => $groupId,
                'is_money'         => $isMoney,
                // seller_id NULL
            ]);
            $imported++;
        }

        Cache::forget('accounting.admin.accounts');

        return redirect()->route('admin.accounting.coa')
            ->with('success', __(":n accounts imported successfully.", ['n' => $imported]));
    }

    private function parseBool($val): bool
    {
        $v = strtolower(trim((string)$val));
        return in_array($v, ['1','true','yes','y'], true);
    }
}