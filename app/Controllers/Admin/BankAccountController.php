<?php

namespace App\Http\Controllers\Admin\Addons;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Account;
use App\Models\Accounting\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BankAccountController extends Controller
{
    public function index(Request $request)
    {
        $bankAccounts = BankAccount::with('account')->latest()->get();

        // ✅ Only accounts flagged as "money"
        $accounts = Account::active()
            ->where('is_money', true)
            ->orderBy('name')
            ->get();

        // Attach live balance to each bank
        foreach ($bankAccounts as $bank) {
            $bank->calculated_balance = $bank->account?->journalItems()
                ->selectRaw("
                    SUM(CASE WHEN type = 'debit'  THEN amount ELSE 0 END)
                  - SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END) as balance
                ")
                ->value('balance') ?? 0;
        }

        return view('addons.accounting.bank_mng', compact('bankAccounts', 'accounts'));
    }

    public function store(Request $request)
    {
        // ✅ Ensure selected account exists AND is a money account
        $rules = [
            'account_id'       => [
                'required',
                Rule::exists('acc_accounts', 'id')->where(fn($q) => $q->where('is_money', 1)),
            ],
            'name'             => 'required|string|max:255',
            'account_number'   => 'nullable|string|max:255',
            'bank_holder_name' => 'nullable|string|max:255',
            'contact_number'   => 'nullable|string|max:255',
            'address'          => 'nullable|string|max:500',
            'opening_balance'  => 'nullable|numeric',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            if ($request->ajax()) {
                // SweetAlert-friendly
                return response()->json([
                    'status'  => 'fail',
                    'title'   => __('Validation error'),
                    'message' => $validator->errors()->first(),
                    'errors'  => $validator->errors(),
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        DB::transaction(function () use ($request) {
            $bank = new BankAccount();
            $bank->account_id      = $request->account_id;
            $bank->bank_name       = $request->name;
            $bank->account_number  = $request->account_number;
            $bank->holder_name     = $request->bank_holder_name;
            $bank->contact_number  = $request->contact_number;
            $bank->address         = $request->address;
            $bank->opening_balance = $request->opening_balance ?? 0;
            $bank->current_balance = $request->opening_balance ?? 0;
            $bank->save();
        });

        if ($request->ajax()) {
            // SweetAlert-friendly
            return response()->json([
                'status'  => 'success',
                'title'   => __('Created!'),
                'message' => __('Bank account created successfully.'),
                'url'     => route('admin.accounting.bank_accounts.index'),
            ]);
        }

        return redirect()
            ->route('admin.accounting.bank_accounts.index')
            ->with('success', __('Bank account created successfully.'));
    }

    public function destroy($id, Request $request)
    {
        $account = BankAccount::findOrFail($id);
        $account->delete();

        // If called by SweetAlert ajax deleter, return JSON
        if ($request->ajax()) {
            return response()->json([
                'status'  => 'success',
                'title'   => __('Deleted!'),
                'message' => __('Bank Account deleted successfully.'),
                'url'     => null,
            ]);
        }

        return back()->with('success', __('Bank Account deleted successfully'));
    }
}