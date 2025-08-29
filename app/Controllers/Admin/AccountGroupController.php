<?php

namespace App\Http\Controllers\Admin\Addons;

use App\Http\Controllers\Controller;
use App\Models\Accounting\AccountGroup;   // ✅ use the model here
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AccountGroupController extends Controller
{
    public function index()
    {
        // Admin lists ONLY global groups
        $groups = AccountGroup::whereNull('seller_id')
                    ->orderBy('name')
                    ->get();

        return view('addons.accounting.account_groups_index', compact('groups'));
    }

    public function create()
    {
        return view('addons.accounting.account_groups_create');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);

        // Ensure created as global (admin-owned)
        AccountGroup::create([
            'name' => $request->name,
            'seller_id' => null,
        ]);

        return redirect()->route('admin.accounting.groups.index')
            ->with('success', 'Group created successfully.');
    }

    public function edit($id)
    {
        // Admin can edit only global groups
        $group = AccountGroup::whereNull('seller_id')->findOrFail($id);

        return view('addons.accounting.account_groups_edit', compact('group'));
    }

    public function update(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:255']);

        $group = AccountGroup::whereNull('seller_id')->findOrFail($id);
        $group->update(['name' => $request->name]);

        return redirect()->route('admin.accounting.groups.index')
            ->with('success', 'Group updated successfully.');
    }

    public function destroy($id)
    {
        try {
            // Admin can delete only global groups
            $group = AccountGroup::whereNull('seller_id')->findOrFail($id);
            $group->delete();

            return response()->json(['message' => __('Group deleted successfully.')], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => __('Something went wrong.')], 500);
        }
    }

    public function importView()
    {
        return view('addons.accounting.account_groups_import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $collection = Excel::toCollection(null, $request->file('file'));
        $rows = $collection[0] ?? collect();

        $imported = 0;
        $skipped  = 0;

        foreach ($rows->skip(1) as $row) {
            $name = trim((string)($row[0] ?? ''));

            if ($name === '' ||
                AccountGroup::whereNull('seller_id')->where('name', $name)->exists()) {
                $skipped++;
                continue;
            }

            AccountGroup::create([
                'name' => $name,
                'seller_id' => null, // enforce global
            ]);
            $imported++;
        }

        return redirect()->route('admin.accounting.groups.index')
            ->with('success', "$imported imported, $skipped skipped.");
    }
}