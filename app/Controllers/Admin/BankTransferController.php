<?php

namespace App\Http\Controllers\Admin\Addons;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Account;
use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\JournalItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankTransferController extends Controller
{
    public function index()
    {
        $accounts = Account::active()  // add this
            ->where('type', 'asset')
            ->where(function ($q) {
                $q->where('name', 'like', '%Cash%')
                    ->orWhere('name', 'like', '%Bank%')
                    ->orWhere('name', 'like', '%Zaad%')
                    ->orWhere('name', 'like', '%eDahab%');
            })
            ->get();

        $transfers = JournalEntry::with(['journalItems.account'])
            ->where('type', 'transfer')
            ->latest()
            ->get();

        return view('addons.accounting.bank_transfer', compact('transfers', 'accounts'));
    }

    public function create()
    {
        $accounts = Account::where('type', 'asset')
            ->whereHas('accountGroup', function ($q) {
                $q->where('name', 'like', '%Cash%')
                  ->orWhere('name', 'like', '%Bank%')
                  ->orWhere('name', 'like', '%Zaad%')
                  ->orWhere('name', 'like', '%eDahab%');
            })
            ->get();

        return view('addons.accounting.transfer_create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'from_account_id' => 'required|different:to_account_id|exists:acc_accounts,id',
            'to_account_id' => 'required|exists:acc_accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'reference' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();

        try {
            $entry = new JournalEntry();
            $entry->date = $request->date;
            $entry->type = 'transfer';
            $entry->journal_number = JournalEntry::nextNumber(); // make sure this exists!
            $entry->reference = $request->reference;
            $entry->description = $request->description ?? 'Fund Transfer';
            $entry->save();

            // Credit
            JournalItem::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $request->from_account_id,
                'type' => 'credit',
                'amount' => $request->amount,
            ]);

            // Debit
            JournalItem::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $request->to_account_id,
                'type' => 'debit',
                'amount' => $request->amount,
            ]);

            DB::commit();

            return redirect()->route('admin.accounting.transfers.index')->with('success', __('Transfer recorded successfully.'));
        } catch (\Exception $e) {
            DB::rollBack();
            dd('Transfer failed: ' . $e->getMessage());
        }
    }

}