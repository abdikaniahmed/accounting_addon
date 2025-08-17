<?php

namespace App\Http\Controllers\Admin\Addons;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use App\Models\Accounting\{Asset, Account};
use App\Services\JournalService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Brian2694\Toastr\Facades\Toastr;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $q = Asset::with(['assetAccount','paymentAccount'])
            ->orderByDesc('id');

        if ($request->filled('search')) {
            $s = $request->search;
            $q->where(function($x) use ($s) {
                $x->where('asset_name','like',"%$s%")
                  ->orWhere('asset_code','like',"%$s%");
            });
        }
        if ($request->filled('status')) {
            $q->where('is_active', $request->status === 'active');
        }

        $assets = $q->paginate(15)->withQueryString();
        return view('addons.accounting.assets_index', compact('assets'));
    }

    public function create()
    {
        $assetAccounts   = Account::where('type','asset')->orderBy('code')->orderBy('name')->pluck('name','id');
        $moneyAccounts   = Account::where('is_money', true)->orderBy('name')->pluck('name','id');

        return view('addons.accounting.assets_form', [
            'asset'          => new Asset(['id' => null]),
            'assetAccounts'  => $assetAccounts,
            'moneyAccounts'  => $moneyAccounts,
        ]);
    }

    public function store(Request $request, JournalService $journal)
    {
        $request->validate([
            'asset_name'         => ['required','string','max:255'],
            'asset_code'         => ['nullable','string','max:255', Rule::unique('acc_assets','asset_code')],
            'asset_account_id'   => ['required','exists:acc_accounts,id'],
            'purchase_date'      => ['required','date'],
            'cost'               => ['required','numeric','min:0.01'],
            'payment_account_id' => ['nullable','exists:acc_accounts,id'],
            // depreciation options
            'depreciation_method'=> ['required','in:none,straight_line'],
            'useful_life_months' => ['nullable','integer','min:1'],
            'salvage_value'      => ['nullable','numeric','min:0'],
            'is_active'          => ['nullable','boolean'],
        ]);

        // If method=straight_line, ensure useful_life_months is provided
        if ($request->depreciation_method === 'straight_line' && !$request->filled('useful_life_months')) {
            return back()->withErrors(['useful_life_months' => 'Useful life (months) is required for straight-line depreciation.'])->withInput();
        }

        $asset = Asset::create([
            'asset_name'         => $request->asset_name,
            'asset_code'         => $request->asset_code,
            'asset_account_id'   => (int) $request->asset_account_id,
            'purchase_date'      => $request->purchase_date,
            'cost'               => round((float)$request->cost, 2),
            'payment_account_id' => $request->payment_account_id ? (int)$request->payment_account_id : null,
            'depreciation_method'=> $request->depreciation_method,
            'useful_life_months' => $request->useful_life_months,
            'salvage_value'      => $request->salvage_value ? round((float)$request->salvage_value,2) : null,
            'is_active'          => (bool) $request->boolean('is_active', true),
        ]);

        // Optional: post purchase journal if paid from cash/bank
        if ($asset->payment_account_id) {
            $entry = $journal->create(
                [
                    'date'        => $asset->purchase_date,
                    'type'        => 'asset_purchase',
                    'reference'   => $asset->asset_code,
                    'description' => 'Asset purchase: '.$asset->asset_name,
                ],
                [
                    ['account_id' => $asset->asset_account_id,    'debit' => $asset->cost, 'credit' => 0,             'memo' => 'Capitalize asset'],
                    ['account_id' => $asset->payment_account_id,  'debit' => 0,            'credit' => $asset->cost, 'memo' => 'Cash/Bank'],
                ]
            );
            $asset->update(['journal_entry_id' => $entry->id]);
        }

        Toastr::success(__('Asset recorded successfully.'));
        return redirect()->route('admin.accounting.assets.index');
    }

    public function edit(Asset $asset)
    {
        $assetAccounts  = Account::where('type','asset')->orderBy('code')->orderBy('name')->pluck('name','id');
        $moneyAccounts  = Account::where('is_money', true)->orderBy('name')->pluck('name','id');

        return view('addons.accounting.assets_form', compact('asset','assetAccounts','moneyAccounts'));
    }

    public function update(Request $request, Asset $asset, JournalService $journal)
    {
        $request->validate([
            'asset_name'         => ['required','string','max:255'],
            'asset_code'         => ['nullable','string','max:255', Rule::unique('acc_assets','asset_code')->ignore($asset->id)],
            'asset_account_id'   => ['required','exists:acc_accounts,id'],
            'purchase_date'      => ['required','date'],
            'cost'               => ['required','numeric','min:0.01'],
            'payment_account_id' => ['nullable','exists:acc_accounts,id'],
            'depreciation_method'=> ['required','in:none,straight_line'],
            'useful_life_months' => ['nullable','integer','min:1'],
            'salvage_value'      => ['nullable','numeric','min:0'],
            'is_active'          => ['nullable','boolean'],
        ]);

        if ($request->depreciation_method === 'straight_line' && !$request->filled('useful_life_months')) {
            return back()->withErrors(['useful_life_months' => 'Useful life (months) is required for straight-line depreciation.'])->withInput();
        }

        $asset->update([
            'asset_name'         => $request->asset_name,
            'asset_code'         => $request->asset_code,
            'asset_account_id'   => (int) $request->asset_account_id,
            'purchase_date'      => $request->purchase_date,
            'cost'               => round((float)$request->cost, 2),
            'payment_account_id' => $request->payment_account_id ? (int)$request->payment_account_id : null,
            'depreciation_method'=> $request->depreciation_method,
            'useful_life_months' => $request->useful_life_months,
            'salvage_value'      => $request->salvage_value ? round((float)$request->salvage_value,2) : null,
            'is_active'          => (bool) $request->boolean('is_active', true),
        ]);

        // (Optional) You could re-post/replace the purchase journal here if needed.

        Toastr::success(__('Asset updated successfully.'));
        return redirect()->route('admin.accounting.assets.index');
    }

    public function destroy(Asset $asset)
    {
        // If you want: also reverse/delete journal entry (optional)
        $asset->delete();

        return response()->json([
            'status'  => 'success',
            'message' => __('Asset deleted'),
        ]);
    }
    
    public function postDepreciation(Request $request)
    {
        $date = $request->input('date'); // optional YYYY-MM-DD
        $args = $date ? ['--date' => $date] : [];

        // Run synchronously; command itself is idempotent via the guard table
        Artisan::call('depreciation:post', $args);

        Toastr::success(__('Depreciation posted successfully.'));
        return back();
    }

}