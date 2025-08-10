<?php

namespace App\Http\Controllers\Admin\Addons;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Account;
use App\Models\Accounting\JournalEntry;
use App\Services\JournalService;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;

class BankTransferController extends Controller
{
    public function index()
    {
        $accounts = Account::active()->where('is_money', true)->orderBy('name')->get();

        $transfers = JournalEntry::with(['journalItems.account'])
            ->where('type', 'transfer')->latest()->get();

        return view('addons.accounting.bank_transfer', compact('transfers','accounts'));
    }

    public function create()
    {
        $accounts = Account::active()->where('is_money', true)->orderBy('name')->get();
        return view('addons.accounting.transfer_create', compact('accounts'));
    }

    public function store(Request $request, JournalService $journal)
    {
        $request->validate([
            'from_account_id' => 'required|different:to_account_id|exists:acc_accounts,id',
            'to_account_id'   => 'required|exists:acc_accounts,id',
            'amount'          => 'required|numeric|min:0.01',
            'date'            => 'required|date',
            'reference'       => 'nullable|string|max:255',
            'description'     => 'nullable|string|max:500',
        ]);

        $journal->create(
            [
                'date'        => $request->date,
                'type'        => 'transfer',
                'reference'   => $request->reference,
                'description' => $request->description ?? 'Fund Transfer',
            ],
            [
                // Cr: from
                ['account_id' => $request->from_account_id, 'debit' => 0,                 'credit' => $request->amount, 'memo' => 'Transfer out'],
                // Dr: to
                ['account_id' => $request->to_account_id,   'debit' => $request->amount,  'credit' => 0,                 'memo' => 'Transfer in'],
            ]
        );

        Toastr::success(__('Transfer recorded successfully.'));
        return redirect()->route('admin.accounting.transfers.index');
    }
}