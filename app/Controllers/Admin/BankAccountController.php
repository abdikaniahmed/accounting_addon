<?php

namespace App\Http\Controllers\Admin\Addons;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Account;
use App\Models\Accounting\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class BankAccountController extends Controller
{
    public function index(Request $request)
    {
        $bankAccounts = BankAccount::with('account')->latest()->get();

        $accounts = Account::active()
            ->where('type', 'asset')
            ->where(function ($q) {
                $q->where('name', 'like', '%Cash%')
                    ->orWhere('name', 'like', '%Bank%')
                    ->orWhere('name', 'like', '%Zaad%')
                    ->orWhere('name', 'like', '%eDahab%');
            })
            ->get();

        // Attach live balance to each bank
        foreach ($bankAccounts as $bank) {
            $bank->calculated_balance = $bank->account?->journalItems()
                ->selectRaw("SUM(CASE WHEN type = 'debit' THEN amount ELSE 0 END) - SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END) as balance")
                ->value('balance') ?? 0;
        }

        return view('addons.accounting.bank_mng', compact('bankAccounts', 'accounts'));
    }

    public function store(Request $request)
    {
        $rules = [
            'account_id' => 'required|exists:acc_accounts,id',
            'name' => 'required|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'bank_holder_name' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'opening_balance' => 'nullable|numeric',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'fail',
                    'errors' => $validator->errors()
                ]);
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
            return response()->json([
                'status' => 'success',
                'message' => 'Bank account created successfully'
            ]);
        }

        return redirect()->route('admin.accounting.bank_accounts.index')
            ->with('success', 'Bank account created successfully');
    }

    public function destroy($id)
    {
        $account = BankAccount::findOrFail($id);
        $account->delete();

        return back()->with('success', 'Bank Account deleted successfully');
    }
}