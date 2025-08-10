<?php

namespace App\Http\Controllers\Admin\Addons;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Accounting\QuickExpense;
use App\Models\Accounting\Account;
use App\Services\JournalService;
use Brian2694\Toastr\Facades\Toastr;

class QuickExpensesController extends Controller
{
    public function index()
    {
        $expenses = QuickExpense::with(['expenseAccount', 'paymentAccount', 'journalEntry'])
            ->latest()->get();

        return view('addons.accounting.quick_expenses_index', compact('expenses'));
    }

    public function create()
    {
        $accounts = Account::where('type', 'expense')->pluck('name', 'id');
        $paymentAccounts = Account::where('is_money', true)->pluck('name', 'id');

        return view('addons.accounting.quick_expenses_form', [
            'expense' => new QuickExpense(['id' => null]),
            'accounts' => $accounts,
            'paymentAccounts' => $paymentAccounts
        ]);
    }

    public function store(Request $request, JournalService $journal)
    {
        $request->validate([
            'account_id'          => 'required|exists:acc_accounts,id',
            'payment_account_id'  => 'required|exists:acc_accounts,id',
            'amount'              => 'required|numeric|min:0.01',
            'date'                => 'required|date',
            'description'         => 'nullable|string|max:255',
            'reference'           => 'nullable|string|max:255',
            'vendor'              => 'nullable|string|max:255',
            'bill_file'           => 'nullable|file|mimes:jpg,jpeg,png,pdf,docx,doc|max:2048',
        ]);

        $data = $request->only(
            'account_id','payment_account_id','amount','date','description','reference','vendor'
        );
        if ($request->hasFile('bill_file')) {
            $data['bill_file'] = $request->file('bill_file')->store('uploads/bills');
        }

        // Persist the expense first
        $expense = QuickExpense::create($data);

        // Post to GL
        $entry = $journal->create(
            [
                'date'        => $data['date'],
                'type'        => 'expense',
                'reference'   => $data['reference'] ?? null,
                'description' => trim('Expense - '.($data['vendor'] ?? '').' '.$data['description'] ?? ''),
            ],
            [
                // Dr: Expense
                ['account_id' => $data['account_id'],         'debit'  => $data['amount'], 'credit' => 0, 'memo' => $data['description'] ?? null],
                // Cr: Cash/Bank
                ['account_id' => $data['payment_account_id'], 'debit'  => 0,               'credit' => $data['amount'], 'memo' => 'Payment'],
            ]
        );

        $expense->update(['journal_entry_id' => $entry->id]);

        Toastr::success(__('Expense recorded successfully.'));
        return redirect()->route('admin.accounting.quick_expenses.index');
    }

    public function edit($id)
    {
        $expense = QuickExpense::findOrFail($id);
        $accounts = Account::where('type', 'expense')->pluck('name', 'id');
        $paymentAccounts = Account::where('is_money', true)->pluck('name', 'id');

        return view('addons.accounting.quick_expenses_form', compact('expense','accounts','paymentAccounts'));
    }

    public function update(Request $request, $id, JournalService $journal)
    {
        $expense = QuickExpense::findOrFail($id);

        $request->validate([
            'account_id'          => 'required|exists:acc_accounts,id',
            'payment_account_id'  => 'required|exists:acc_accounts,id',
            'amount'              => 'required|numeric|min:0.01',
            'date'                => 'required|date',
            'description'         => 'nullable|string|max:255',
            'reference'           => 'nullable|string|max:255',
            'vendor'              => 'nullable|string|max:255',
            'bill_file'           => 'nullable|file|mimes:jpg,jpeg,png,pdf,docx,doc|max:2048',
        ]);

        $data = $request->only(
            'account_id','payment_account_id','amount','date','description','reference','vendor'
        );
        if ($request->hasFile('bill_file')) {
            $data['bill_file'] = $request->file('bill_file')->store('uploads/bills');
        }

        $expense->update($data);

        // Replace the GL posting (idempotent)
        if ($expense->journal_entry_id && $expense->journalEntry) {
            $journal->replace(
                $expense->journalEntry,
                [
                    'date'        => $data['date'],
                    'type'        => 'expense',
                    'reference'   => $data['reference'] ?? null,
                    'description' => trim('Expense - '.($data['vendor'] ?? '').' '.$data['description'] ?? ''),
                ],
                [
                    ['account_id' => $data['account_id'],         'debit'  => $data['amount'], 'credit' => 0, 'memo' => $data['description'] ?? null],
                    ['account_id' => $data['payment_account_id'], 'debit'  => 0,               'credit' => $data['amount'], 'memo' => 'Payment'],
                ]
            );
        } else {
            // Backfill if it never posted (rare)
            $entry = $journal->create(
                [
                    'date'        => $data['date'],
                    'type'        => 'expense',
                    'reference'   => $data['reference'] ?? null,
                    'description' => trim('Expense - '.($data['vendor'] ?? '').' '.$data['description'] ?? ''),
                ],
                [
                    ['account_id' => $data['account_id'],         'debit'  => $data['amount'], 'credit' => 0],
                    ['account_id' => $data['payment_account_id'], 'debit'  => 0,               'credit' => $data['amount']],
                ]
            );
            $expense->update(['journal_entry_id' => $entry->id]);
        }

        Toastr::success(__('Expense updated successfully.'));
        return redirect()->route('admin.accounting.quick_expenses.index');
    }

    public function destroy($id, JournalService $journal)
    {
        $expense = QuickExpense::findOrFail($id);

        if ($expense->journal_entry_id && $expense->journalEntry) {
            $journal->delete($expense->journalEntry);
        }

        $expense->delete();

        return response()->json([
            'title'   => __('Deleted!'),
            'message' => __('Expense deleted successfully.'),
            'status'  => 'success'
        ]);
    }
}