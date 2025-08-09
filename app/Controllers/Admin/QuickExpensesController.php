<?php

namespace App\Http\Controllers\Admin\Addons;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Accounting\QuickExpense;
use App\Models\Accounting\Account;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Storage;

class QuickExpensesController extends Controller
{
    public function index()
    {
        $expenses = QuickExpense::with(['expenseAccount', 'paymentAccount'])->latest()->get();
        return view('addons.accounting.quick_expenses_index', compact('expenses'));
    }

public function create()
{
    $accounts = Account::where('type', 'expense')->pluck('name', 'id');

    $paymentAccounts = Account::whereHas('accountGroup', function ($query) {
        $query->where('name', 'like', '%Cash%')
              ->orWhere('name', 'like', '%Bank%')
              ->orWhere('name', 'like', '%Zaad%')
              ->orWhere('name', 'like', '%eDahab%');
    })->pluck('name', 'id');

    return view('addons.accounting.quick_expenses_form', [
        'expense' => new QuickExpense(['id' => null]),
        'accounts' => $accounts,
        'paymentAccounts' => $paymentAccounts
    ]);
}

    public function store(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:acc_accounts,id',
            'payment_account_id' => 'required|exists:acc_accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'description' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:255',
            'vendor' => 'nullable|string|max:255',
            'bill_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf,docx,doc|max:2048',
        ]);

        $data = $request->only([
            'account_id', 'payment_account_id', 'amount', 'date', 'description', 'reference', 'vendor'
        ]);

        if ($request->hasFile('bill_file')) {
            $data['bill_file'] = $request->file('bill_file')->store('uploads/bills');
        }

        QuickExpense::create($data);

        Toastr::success(__('Expense recorded successfully.'));
        return redirect()->route('admin.accounting.quick_expenses.index');
    }

    public function edit($id)
    {
        $expense = QuickExpense::findOrFail($id);
        $accounts = Account::where('type', 'expense')->pluck('name', 'id');
        $paymentAccounts = Account::where('type', 'asset')->pluck('name', 'id');

        return view('addons.accounting.quick_expenses_form', [
            'expense' => $expense,
            'accounts' => $accounts,
            'paymentAccounts' => $paymentAccounts
        ]);
    }

    public function update(Request $request, $id)
    {
        $expense = QuickExpense::findOrFail($id);

        $request->validate([
            'account_id' => 'required|exists:acc_accounts,id',
            'payment_account_id' => 'required|exists:acc_accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'description' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:255',
            'vendor' => 'nullable|string|max:255',
            'bill_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf,docx,doc|max:2048',
        ]);

        $data = $request->only([
            'account_id', 'payment_account_id', 'amount', 'date', 'description', 'reference', 'vendor'
        ]);

        if ($request->hasFile('bill_file')) {
            $data['bill_file'] = $request->file('bill_file')->store('uploads/bills');
        }

        $expense->update($data);

        Toastr::success(__('Expense updated successfully.'));
        return redirect()->route('admin.accounting.quick_expenses.index');
    }

    public function destroy($id)
    {
        $expense = QuickExpense::findOrFail($id);
        $expense->delete();

        return response()->json([
            'title' => __('Deleted!'),
            'message' => __('Expense deleted successfully.'),
            'status' => 'success'
        ]);
    }
}