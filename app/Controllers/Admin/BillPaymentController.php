<?php

namespace App\Http\Controllers\Admin\Addons;

use App\Http\Controllers\Controller;
use App\Models\Accounting\{Bill, Account, BillPayment};
use App\Services\JournalService;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;

class BillPaymentController extends Controller
{
    public function index()
    {
        $payments = BillPayment::with(['bill.vendor','paymentAccount'])
            ->latest('payment_date')    // <-- your table uses payment_date
            ->paginate(20);

        return view('addons.accounting.bill_payments_index', compact('payments'));
    }

    public function create(Bill $bill)
    {
        $bill->load(['payments','vendor']);

        $paid = (float) $bill->payments->sum('amount');
        $due  = max(0, round(((float)$bill->total_amount) - $paid, 2));

        // ✅ ONLY accounts marked as money
        $moneyAccounts = Account::where('is_money', true)
            ->orderByRaw("COALESCE(NULLIF(code,''), 'ZZZ')") // code first if present
            ->orderBy('name')
            ->get(['id','code','name'])
            ->mapWithKeys(fn($a) => [$a->id => trim(($a->code ? $a->code.' - ' : '').$a->name)]);

        return view('addons.accounting.bill_pay', compact('bill','moneyAccounts','paid','due'));
    }

    public function store(Request $request, Bill $bill, JournalService $journal)
    {
        $bill->load('payments');

        $alreadyPaid = (float) $bill->payments->sum('amount');
        $due         = max(0, round(((float)$bill->total_amount) - $alreadyPaid, 2));

        $request->validate([
            'payment_date'       => ['required','date'],
            'payment_account_id' => ['required','exists:acc_accounts,id'],
            'amount'             => ['required','numeric','min:0.01','max:'.$due],
            'reference'          => ['nullable','string','max:255'],
            'description'        => ['nullable','string','max:500'],
        ]);

        $apAccountId = $this->resolveApAccountId();
        if (!$apAccountId) {
            return back()->withErrors(['ap' => 'No suitable Accounts Payable account found.']);
        }

        // Post to GL: Dr A/P, Cr Cash/Bank
        $entry = $journal->create(
            [
                'date'        => $request->payment_date,
                'type'        => 'bill_payment',
                'reference'   => $request->reference,
                'description' => 'Bill payment '.$bill->bill_number,
            ],
            [
                ['account_id' => $apAccountId,                        'debit'  => (float)$request->amount, 'credit' => 0,                         'memo' => 'A/P payment'],
                ['account_id' => (int)$request->payment_account_id,   'debit'  => 0,                        'credit' => (float)$request->amount,  'memo' => 'Cash/Bank'],
            ]
        );

        // ✅ Persist the payment row
        $bill->payments()->create([
            'payment_account_id' => (int) $request->payment_account_id,
            'payment_date'       => $request->payment_date,
            'amount'             => (float) $request->amount,
            'reference'          => $request->reference,
            'description'        => $request->description,
            'journal_entry_id'   => $entry->id,
        ]);

        // Update bill balances
        $newPaid    = $alreadyPaid + (float) $request->amount;
        $newBalance = max(0, round(((float)$bill->total_amount) - $newPaid, 2));
        $status     = $newBalance <= 0 ? 'paid' : ($newPaid > 0 ? 'partially_paid' : 'unpaid');

        $bill->update([
            'balance_due'      => $newBalance,
            'status'           => $status,
            'journal_entry_id' => $entry->id,
        ]);

        Toastr::success(__('Payment recorded.'));
        return redirect()->route('admin.accounting.bills.index');
    }

    private function resolveApAccountId(): ?int
    {
        return Account::where('type','liability')
            ->where(function ($q) {
                $q->where('name','like','%accounts payable%')
                  ->orWhere('name','like','%payable%')
                  ->orWhere('name','like','%creditors%');
            })
            ->value('id')
            ?? Account::where('type','liability')->value('id');
    }
}