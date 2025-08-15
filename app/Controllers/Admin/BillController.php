<?php

namespace App\Http\Controllers\Admin\Addons;

use App\Http\Controllers\Controller;
use App\Models\Accounting\{Bill, BillItem, Account, Contact};
use App\Services\JournalService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Brian2694\Toastr\Facades\Toastr;

class BillController extends Controller
{
    /** List bills */
    public function index(Request $request)
    {
        $q = Bill::with(['vendor','items','payments'])->latest();

        // optional quick filters
        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }
        if ($request->filled('vendor')) {
            $q->whereHas('vendor', function ($v) use ($request) {
                $v->where('name','like','%'.$request->vendor.'%');
            });
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $q->where(function ($x) use ($s) {
                $x->where('bill_number','like',"%$s%")
                  ->orWhere('notes','like',"%$s%");
            });
        }

        $bills   = $q->paginate(15)->withQueryString();
        $vendors = Contact::where('type','vendor')->orderBy('name')->pluck('name','id');

        return view('addons.accounting.bills_index', compact('bills','vendors'));
    }

    /** Show create form */
    public function create()
    {
        $vendors = Contact::where('type', 'vendor')->orderBy('name')->pluck('name', 'id');

        // show "CODE - Name" if code exists
        $expenseAccounts = Account::orderBy('code')->orderBy('name')
            ->get(['id','code','name','type'])
            ->mapWithKeys(function ($a) {
                $label = trim(($a->code ? ($a->code.' - ') : '').$a->name);
                return [$a->id => $label];
            });

        return view('addons.accounting.bills_form', compact('expenseAccounts','vendors'));
    }

    /** Persist a new bill */
    public function store(Request $request, JournalService $journal)
    {
        $request->validate([
            'vendor_id'                 => ['required', Rule::exists('acc_contacts','id')],
            'bill_number'               => ['required','string','max:255','unique:acc_bills,bill_number'],
            'bill_date'                 => ['required','date'],
            'due_date'                  => ['nullable','date','after_or_equal:bill_date'],
            'items'                     => ['required','array','min:1'],
            'items.*.account_id'        => ['required','exists:acc_accounts,id'],
            'items.*.quantity'          => ['required','numeric','min:0.0001'],
            'items.*.unit_price'        => ['required','numeric','min:0'],
            'items.*.description'       => ['nullable','string','max:255'],
            'notes'                     => ['nullable','string'],
        ]);

        // Build items & totals
        $itemsData   = [];
        $totalAmount = 0.0;

        foreach ($request->items as $row) {
            $qty   = (float) $row['quantity'];
            $price = (float) $row['unit_price'];
            $line  = round($qty * $price, 2);

            $itemsData[] = [
                'account_id'  => (int) $row['account_id'],
                'description' => $row['description'] ?? null,
                'quantity'    => $qty,
                'unit_price'  => $price,
                'total'       => $line,
            ];
            $totalAmount += $line;
        }

        // Auto-detect Accounts Payable
        $apAccountId = $this->resolveApAccountId();
        if (!$apAccountId) {
            return back()
                ->withErrors(['ap' => 'No suitable Accounts Payable (liability) account found. Create one like "Accounts Payable" (code 2100).'])
                ->withInput();
        }

        DB::transaction(function () use ($request, $itemsData, $totalAmount, $journal, $apAccountId) {
            /** @var Bill $bill */
            $bill = Bill::create([
                'vendor_id'     => (int) $request->vendor_id,
                'bill_number'   => $request->bill_number,
                'bill_date'     => $request->bill_date,
                'due_date'      => $request->due_date,
                'total_amount'  => round($totalAmount, 2),
                'balance_due'   => round($totalAmount, 2),
                'status'        => 'unpaid',
                'notes'         => $request->notes,
            ]);

            foreach ($itemsData as $i) {
                $bill->items()->create($i);
            }

            // Journal entry: Dr expense lines, Cr A/P
            $groupedDebits = [];
            foreach ($itemsData as $i) {
                $groupedDebits[$i['account_id']] = ($groupedDebits[$i['account_id']] ?? 0) + $i['total'];
            }

            $lines = [];
            foreach ($groupedDebits as $accId => $amt) {
                $lines[] = ['account_id' => $accId, 'debit' => round($amt,2), 'credit' => 0, 'memo' => 'Bill line'];
            }
            $lines[] = ['account_id' => $apAccountId, 'debit' => 0, 'credit' => round($totalAmount,2), 'memo' => 'Accounts Payable'];

            $entry = $journal->create(
                [
                    'date'        => $bill->bill_date,
                    'type'        => 'bill',
                    'reference'   => $bill->bill_number,
                    'description' => 'Bill '.$bill->bill_number.' (Vendor #'.$bill->vendor_id.')',
                ],
                $lines
            );

            $bill->update(['journal_entry_id' => $entry->id]);
        });

        Toastr::success(__('Bill recorded successfully.'));
        return redirect()->route('admin.accounting.bills.index');
    }

    /** Edit form */
    public function edit(Bill $bill)
    {
        $bill->load(['items','vendor']);
        $vendors = Contact::where('type', 'vendor')->orderBy('name')->pluck('name','id');

        $expenseAccounts = Account::orderBy('code')->orderBy('name')
            ->get(['id','code','name','type'])
            ->mapWithKeys(function ($a) {
                $label = trim(($a->code ? ($a->code.' - ') : '').$a->name);
                return [$a->id => $label];
            });

        return view('addons.accounting.bills_form', compact('bill','vendors','expenseAccounts'));
    }

    /** Persist updates */
    public function update(Request $request, Bill $bill, JournalService $journal)
    {
        $request->validate([
            'vendor_id'                 => ['required', Rule::exists('acc_contacts','id')],
            'bill_number'               => ['required','string','max:255','unique:acc_bills,bill_number,'.$bill->id],
            'bill_date'                 => ['required','date'],
            'due_date'                  => ['nullable','date','after_or_equal:bill_date'],
            'items'                     => ['required','array','min:1'],
            'items.*.account_id'        => ['required','exists:acc_accounts,id'],
            'items.*.quantity'          => ['required','numeric','min:0.0001'],
            'items.*.unit_price'        => ['required','numeric','min:0'],
            'items.*.description'       => ['nullable','string','max:255'],
            'notes'                     => ['nullable','string'],
        ]);

        $itemsData   = [];
        $totalAmount = 0.0;
        foreach ($request->items as $row) {
            $qty   = (float) $row['quantity'];
            $price = (float) $row['unit_price'];
            $line  = round($qty * $price, 2);
            $itemsData[] = [
                'account_id'  => (int) $row['account_id'],
                'description' => $row['description'] ?? null,
                'quantity'    => $qty,
                'unit_price'  => $price,
                'total'       => $line,
            ];
            $totalAmount += $line;
        }

        $apAccountId = $this->resolveApAccountId();
        if (!$apAccountId) {
            return back()->withErrors(['ap' => 'No suitable Accounts Payable (liability) account found.'])->withInput();
        }

        DB::transaction(function () use ($request, $bill, $itemsData, $totalAmount, $journal, $apAccountId) {
            // Update bill
            $bill->update([
                'vendor_id'     => (int) $request->vendor_id,
                'bill_number'   => $request->bill_number,
                'bill_date'     => $request->bill_date,
                'due_date'      => $request->due_date,
                'total_amount'  => round($totalAmount, 2),
                'notes'         => $request->notes,
            ]);

            // Replace items
            $bill->items()->delete();
            foreach ($itemsData as $i) {
                $bill->items()->create($i);
            }

            // Recompute balance & status based on payments
            $paid    = (float) ($bill->payments()->sum('amount') ?? 0);
            $balance = max(0, round($totalAmount - $paid, 2));
            $status  = $balance <= 0 ? 'paid' : ($paid > 0 ? 'partially_paid' : 'unpaid');

            $bill->update(['balance_due' => $balance, 'status' => $status]);

            // Post new journal (simple approach)
            $groupedDebits = [];
            foreach ($itemsData as $i) {
                $groupedDebits[$i['account_id']] = ($groupedDebits[$i['account_id']] ?? 0) + $i['total'];
            }
            $lines = [];
            foreach ($groupedDebits as $accId => $amt) {
                $lines[] = ['account_id' => $accId, 'debit' => round($amt,2), 'credit' => 0, 'memo' => 'Bill line'];
            }
            $lines[] = ['account_id' => $apAccountId, 'debit' => 0, 'credit' => round($totalAmount,2), 'memo' => 'Accounts Payable'];

            $entry = $journal->create(
                [
                    'date'        => $bill->bill_date,
                    'type'        => 'bill_update',
                    'reference'   => $bill->bill_number,
                    'description' => 'Bill update '.$bill->bill_number.' (Vendor #'.$bill->vendor_id.')',
                ],
                $lines
            );

            $bill->update(['journal_entry_id' => $entry->id]);
        });

        Toastr::success(__('Bill updated.'));
        return redirect()->route('admin.accounting.bills.index');
    }

    /** Delete (AJAX) */
    public function destroy(Bill $bill)
    {
        $bill->delete();
        return response()->json(['status' => 'success']);
    }

    /** Prefer code 2100, then names, then any liability */
    private function resolveApAccountId(): ?int
    {
        $byCode = Account::where('type','liability')->where('code','2100')->value('id');
        if ($byCode) return (int) $byCode;

        $byName = Account::where('type','liability')
            ->where(function ($q) {
                $q->where('name','like','%accounts payable%')
                  ->orWhere('name','like','%account payable%')
                  ->orWhere('name','like','%payable%')
                  ->orWhere('name','like','%creditors%');
            })
            ->orderByRaw("CASE 
                WHEN name LIKE '%accounts payable%' THEN 1
                WHEN name LIKE '%account payable%'  THEN 2
                WHEN name LIKE '%payable%'          THEN 3
                ELSE 9 END")
            ->value('id');
        if ($byName) return (int) $byName;

        $liab = Account::where('type','liability')->value('id');
        return $liab ? (int) $liab : null;
    }
}