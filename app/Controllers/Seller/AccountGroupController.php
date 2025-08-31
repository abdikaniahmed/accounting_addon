<?php

namespace App\Http\Controllers\Seller\Addons;

use App\Http\Controllers\Controller;
use App\Models\Accounting\AccountGroup;   // ✅
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;

class AccountGroupController extends Controller
{
    public function index()
    {
        try {
            // Seller sees global(NULL) + own
            $groups = AccountGroup::onlyOwn()
                        ->orderBy('name')
                        ->get();

            return view('addons.accountingSeller.account_groups_index', compact('groups'));
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }

    public function create()
    {
        return view('addons.accountingSeller.account_groups_create');
    }

    public function store(Request $request)
    {
        if (config('app.demo_mode')) {
            Toastr::info(__('This function is disabled in demo server.'));
            return back();
        }

        $request->validate(['name' => 'required|string|max:255']);

        try {
            // Model boot will stamp seller_id for seller users
            AccountGroup::create(['name' => $request->name]);

            Toastr::success(__('Group created successfully.'));
            return redirect()->route('seller.accounting.groups.index');
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back()->withInput();
        }
    }

    public function edit($id)
    {
        $sellerId = optional(Sentinel::getUser())->id;

        // Sellers can edit only their OWN groups
        $group = AccountGroup::where('id', $id)
                    ->where('seller_id', $sellerId)
                    ->firstOrFail();

        return view('addons.accountingSeller.account_groups_edit', compact('group'));
    }

    public function update(Request $request, $id)
    {
        if (config('app.demo_mode')) {
            Toastr::info(__('This function is disabled in demo server.'));
            return back();
        }

        $request->validate(['name' => 'required|string|max:255']);

        $sellerId = optional(Sentinel::getUser())->id;

        $group = AccountGroup::where('id', $id)
                    ->where('seller_id', $sellerId)
                    ->firstOrFail();

        $group->update(['name' => $request->name]);

        Toastr::success(__('Group updated successfully.'));
        return redirect()->route('seller.accounting.groups.index');
    }

    public function destroy($id)
    {
        if (config('app.demo_mode')) {
            return response()->json([
                'message' => __('This function is disabled in demo server.')
            ], 422);
        }

        try {
            $sellerId = optional(Sentinel::getUser())->id;

            // Sellers can delete only their OWN groups
            $group = AccountGroup::where('id', $id)
                        ->where('seller_id', $sellerId)
                        ->firstOrFail();

            $group->delete();

            return response()->json(['message' => __('Group deleted successfully.')], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => __('Something went wrong.')], 500);
        }
    }

    public function importView()
    {
        return view('addons.accountingSeller.account_groups_import');
    }

    public function import(Request $request)
    {
        if (config('app.demo_mode')) {
            Toastr::info(__('This function is disabled in demo server.'));
            return back();
        }

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $sellerId = optional(Sentinel::getUser())->id;

        DB::beginTransaction();
        try {
            $collection = Excel::toCollection(null, $request->file('file'));
            $rows = $collection[0] ?? collect();

            $imported = 0;
            $skipped  = 0;

            foreach ($rows->skip(1) as $row) {
                $name = trim((string)($row[0] ?? ''));

                if ($name === '') { $skipped++; continue; }

                // Uniqueness within seller space (global can have same name)
                $exists = AccountGroup::where('name', $name)
                            ->where('seller_id', $sellerId)
                            ->exists();

                if ($exists) { $skipped++; continue; }

                AccountGroup::create([
                    'name' => $name,
                    'seller_id' => $sellerId, // force ownership on import
                ]);

                $imported++;
            }

            DB::commit();
            Toastr::success(__(':i imported, :s skipped.', ['i'=>$imported,'s'=>$skipped]));
            return redirect()->route('seller.accounting.groups.index');

        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage());
            return back();
        }
    }
}