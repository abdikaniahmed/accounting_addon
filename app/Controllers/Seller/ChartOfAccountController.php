<?php

namespace App\Http\Controllers\Seller\Addons;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Account;
use App\Models\Accounting\AccountGroup;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Brian2694\Toastr\Facades\Toastr;

class ChartOfAccountController extends Controller
{
    /** Seller sees ONLY their own accounts */
    public function index()
    {
        try {
            $accounts = Account::onlyOwn()
                ->orderByDesc('is_money')
                ->orderBy('name')
                ->get();

            return view('addons.accountingSeller.chart_of_accounts', compact('accounts'));
        } catch (\Throwable $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }

    /** Create: seller can pick ONLY their own groups */
    public function create()
    {
        $groups = AccountGroup::onlyOwn()->orderBy('name')->get();
        return view('addons.accountingSeller.account_form', compact('groups'));
    }

    /** Store: force ownership to current seller */
    public function store(Request $request)
    {
        if (config('app.demo_mode')) {
            Toastr::info(__('This function is disabled in demo server.'));
            return back();
        }

        $request->validate([
            'name'             => 'required|string|max:255',
            'type'             => 'required|in:asset,liability,equity,revenue,expense',
            'account_group_id' => 'nullable|integer',
            'is_money'         => 'sometimes|boolean',
        ]);

        $sellerId = optional(Sentinel::getUser())->id;

        // Guard: selected group must belong to the seller
        $groupId = $request->account_group_id;
        if ($groupId) {
            AccountGroup::onlyOwn()->where('id', $groupId)->firstOrFail();
        }

        try {
            Account::create([
                'name'             => $request->name,
                'type'             => $request->type,
                'code'             => $request->code,
                'is_active'        => $request->has('is_active'),
                'account_group_id' => $groupId,
                'is_money'         => $request->boolean('is_money'),
                'seller_id'        => $sellerId, // enforce ownership
            ]);

            Cache::forget("accounting.seller.{$sellerId}.accounts");
            Toastr::success(__('Account created successfully.'));
            return redirect()->route('seller.accounting.coa.index');
        } catch (\Throwable $e) {
            Toastr::error($e->getMessage());
            return back()->withInput();
        }
    }

    /** Edit ONLY own account; groups list = own */
    public function edit($id)
    {
        $account = Account::onlyOwn()->where('id', $id)->firstOrFail();
        $groups  = AccountGroup::onlyOwn()->orderBy('name')->get();

        return view('addons.accountingSeller.account_form', compact('account', 'groups'));
    }

    /** Update ONLY own account */
    public function update(Request $request, $id)
    {
        if (config('app.demo_mode')) {
            Toastr::info(__('This function is disabled in demo server.'));
            return back();
        }

        $request->validate([
            'name'             => 'required|string|max:255',
            'type'             => 'required|in:asset,liability,equity,revenue,expense',
            'account_group_id' => 'nullable|integer',
            'is_money'         => 'sometimes|boolean',
        ]);

        $sellerId = optional(Sentinel::getUser())->id;

        $account = Account::onlyOwn()->where('id', $id)->firstOrFail();

        $groupId = $request->account_group_id;
        if ($groupId) {
            AccountGroup::onlyOwn()->where('id', $groupId)->firstOrFail();
        }

        $account->update([
            'name'             => $request->name,
            'type'             => $request->type,
            'code'             => $request->code,
            'is_active'        => $request->has('is_active'),
            'account_group_id' => $groupId,
            'is_money'         => $request->boolean('is_money'),
        ]);

        Cache::forget("accounting.seller.{$sellerId}.accounts");

        Toastr::success(__('Account updated successfully.'));
        return redirect()->route('seller.accounting.coa.index');
    }

    /** Delete ONLY own account */
    public function destroy($id)
    {
        if (config('app.demo_mode')) {
            return response()->json(['message' => __('This function is disabled in demo server.')], 422);
        }

        $sellerId = optional(Sentinel::getUser())->id;

        $account = Account::onlyOwn()->where('id', $id)->firstOrFail();
        $account->delete();

        Cache::forget("accounting.seller.{$sellerId}.accounts");

        return response()->json(['message' => __('Account deleted successfully.')], 200);
    }

    public function importView()
    {
        return view('addons.accountingSeller.chart_of_accounts_import');
    }

    /** Import into seller’s OWN space (and auto-create OWN groups) */
    public function import(Request $request)
    {
        if (config('app.demo_mode')) {
            Toastr::info(__('This function is disabled in demo server.'));
            return back();
        }

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $sellerId = optional(Sentinel::getUser())->id;

        DB::beginTransaction();
        try {
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

                if ($name === '' || !in_array($type, ['asset','liability','equity','revenue','expense'], true)) {
                    continue;
                }

                // OWN group
                $groupId = null;
                if ($group !== '') {
                    $groupModel = AccountGroup::onlyOwn()
                        ->where('name', $group)
                        ->first();

                    if (!$groupModel) {
                        $groupModel = AccountGroup::create([
                            'name'      => $group,
                            'seller_id' => $sellerId,
                        ]);
                    }
                    $groupId = $groupModel->id;
                }

                // Avoid duplicates in seller space
                $exists = Account::onlyOwn()->where('name', $name)->exists();
                if ($exists) continue;

                Account::create([
                    'name'             => $name,
                    'type'             => $type,
                    'code'             => $code,
                    'is_active'        => $active,
                    'account_group_id' => $groupId,
                    'is_money'         => $isMoney,
                    'seller_id'        => $sellerId,
                ]);

                $imported++;
            }

            DB::commit();

            Cache::forget("accounting.seller.{$sellerId}.accounts");
            Toastr::success(__(':n accounts imported successfully.', ['n' => $imported]));
            return redirect()->route('seller.accounting.coa.index');
        } catch (\Throwable $e) {
            DB::rollBack();
            Toastr::error($e->getMessage());
            return back();
        }
    }

    private function parseBool($val): bool
    {
        $v = strtolower(trim((string)$val));
        return in_array($v, ['1','true','yes','y'], true);
    }
}