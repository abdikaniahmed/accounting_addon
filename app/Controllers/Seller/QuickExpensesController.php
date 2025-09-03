<?php

namespace App\Http\Controllers\Seller\Addons;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Accounting\QuickExpense;
use App\Models\Accounting\Account;
use App\Services\JournalService;
use Illuminate\Support\Facades\DB;

class QuickExpensesController extends Controller
{
    /** List: only rows for the logged-in seller */
    public function index(Request $request)
    {
        $expenses = QuickExpense::onlyOwn()
            ->with(['expenseAccount', 'paymentAccount', 'journalEntry'])
            ->betweenDates($request->get('start'), $request->get('end'))
            ->search($request->get('q'))
            ->latest()
            ->get();

        return view('addons.accountingSeller.quick_expenses_index', compact('expenses'));
    }

    /** Create form: seller can pick from their own accounts only */
    public function create()
    {
        $accounts = Account::onlyOwn()
            ->where('type', 'expense')->orderBy('name')->pluck('name', 'id');

        $paymentAccounts = Account::onlyOwn()
            ->where('is_money', true)->orderBy('name')->pluck('name', 'id');

        return view('addons.accountingSeller.quick_expenses_form', [
            'expense'         => new QuickExpense(['id' => null]),
            'accounts'        => $accounts,
            'paymentAccounts' => $paymentAccounts,
        ]);
    }

    /** Store: auto-stamps seller_id via model boot (or you can set it explicitly) */
    public function store(Request $request, JournalService $journal)
    {
        $request->validate([
            'account_id'          => 'required|exists:acc_accounts,id',
            'payment_account_id'  => 'required|exists:acc_accounts,id',
            'amount'              => 'required|numeric|min:0.01',
            'date'                => 'required|date',
            'description'         => 'nullable|string|max:1000',
            'reference'           => 'nullable|string|max:255',
            'vendor'              => 'nullable|string|max:255',
            'bill_file'           => 'nullable|file|mimes:jpg,jpeg,png,pdf,docx,doc|max:2048',
        ]);

        $data = $request->only('account_id','payment_account_id','amount','date','description','reference','vendor');

        if ($request->hasFile('bill_file')) {
            $data['bill_file'] = $request->file('bill_file')->store('uploads/bills');
        }

        DB::beginTransaction();
        try {
            $expense = QuickExpense::create($data);

            $entry = $journal->create(
                [
                    'date'        => $data['date'],
                    'type'        => 'expense',
                    'reference'   => $data['reference'] ?? null,
                    'description' => trim('Expense - '.($data['vendor'] ?? '').' '.($data['description'] ?? '')),
                ],
                [
                    ['account_id' => $data['account_id'],         'debit' => $data['amount'], 'credit' => 0,               'memo' => $data['description'] ?? null],
                    ['account_id' => $data['payment_account_id'], 'debit' => 0,               'credit' => $data['amount'], 'memo' => 'Payment'],
                ]
            );

            $expense->update(['journal_entry_id' => $entry->id]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', __('Could not save expense: ').$e->getMessage())->withInput();
        }

        return redirect()->route('seller.accounting.quick_expenses.index')
            ->with('success', __('Expense recorded successfully.'));
    }

    public function edit($id)
    {
        $expense = QuickExpense::onlyOwn()->findOrFail($id);

        $accounts = Account::onlyOwn()
            ->where('type', 'expense')->orderBy('name')->pluck('name', 'id');

        $paymentAccounts = Account::onlyOwn()
            ->where('is_money', true)->orderBy('name')->pluck('name', 'id');

        return view('addons.accountingSeller.quick_expenses_form', compact('expense','accounts','paymentAccounts'));
    }

    /** Update: replace posting if needed */
    public function update(Request $request, $id, JournalService $journal)
    {
        $expense = QuickExpense::onlyOwn()->findOrFail($id);

        $request->validate([
            'account_id'          => 'required|exists:acc_accounts,id',
            'payment_account_id'  => 'required|exists:acc_accounts,id',
            'amount'              => 'required|numeric|min:0.01',
            'date'                => 'required|date',
            'description'         => 'nullable|string|max:1000',
            'reference'           => 'nullable|string|max:255',
            'vendor'              => 'nullable|string|max:255',
            'bill_file'           => 'nullable|file|mimes:jpg,jpeg,png,pdf,docx,doc|max:2048',
        ]);

        $data = $request->only('account_id','payment_account_id','amount','date','description','reference','vendor');
        if ($request->hasFile('bill_file')) {
            $data['bill_file'] = $request->file('bill_file')->store('uploads/bills');
        }

        DB::beginTransaction();
        try {
            $expense->update($data);

            if ($expense->journal_entry_id && $expense->journalEntry) {
                $journal->replace(
                    $expense->journalEntry,
                    [
                        'date'        => $data['date'],
                        'type'        => 'expense',
                        'reference'   => $data['reference'] ?? null,
                        'description' => trim('Expense - '.($data['vendor'] ?? '').' '.($data['description'] ?? '')),
                    ],
                    [
                        ['account_id' => $data['account_id'],         'debit' => $data['amount'], 'credit' => 0,               'memo' => $data['description'] ?? null],
                        ['account_id' => $data['payment_account_id'], 'debit' => 0,               'credit' => $data['amount'], 'memo' => 'Payment'],
                    ]
                );
            } else {
                $entry = $journal->create(
                    [
                        'date'        => $data['date'],
                        'type'        => 'expense',
                        'reference'   => $data['reference'] ?? null,
                        'description' => trim('Expense - '.($data['vendor'] ?? '').' '.($data['description'] ?? '')),
                    ],
                    [
                        ['account_id' => $data['account_id'],         'debit' => $data['amount'], 'credit' => 0],
                        ['account_id' => $data['payment_account_id'], 'debit' => 0,               'credit' => $data['amount']],
                    ]
                );
                $expense->update(['journal_entry_id' => $entry->id]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', __('Could not update expense: ').$e->getMessage())->withInput();
        }

        return redirect()->route('seller.accounting.quick_expenses.index')
            ->with('success', __('Expense updated successfully.'));
    }

    public function destroy($id, JournalService $journal)
    {
        $expense = QuickExpense::onlyOwn()->findOrFail($id);

        DB::transaction(function () use ($expense, $journal) {
            if ($expense->journal_entry_id && $expense->journalEntry) {
                $journal->delete($expense->journalEntry);
            }
            $expense->delete();
        });

        return response()->json([
            'title'   => __('Deleted!'),
            'message' => __('Expense deleted successfully.'),
            'status'  => 'success'
        ]);
    }
}