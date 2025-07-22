<?php

namespace App\Http\Controllers\Admin\Addons;

use App\Http\Controllers\Controller;
use App\Models\Accounting\AccountGroup;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AccountGroupController extends Controller
{
    public function index()
    {
        $groups = AccountGroup::orderBy('name')->get();
        return view('addons.accounting.account_groups_index', compact('groups'));
    }

    public function create()
    {
        return view('addons.accounting.account_groups_create');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        AccountGroup::create(['name' => $request->name]);
        return redirect()->route('admin.accounting.groups.index')->with('success', 'Group created successfully.');
    }

    public function edit($id)
    {
        $group = AccountGroup::findOrFail($id);
        return view('addons.accounting.account_groups_edit', compact('group'));
    }

    public function update(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $group = AccountGroup::findOrFail($id);
        $group->update(['name' => $request->name]);
        return redirect()->route('admin.accounting.groups.index')->with('success', 'Group updated successfully.');
    }

    public function destroy($id)
    {
        try {
            $group = AccountGroup::findOrFail($id);
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

        $path = $request->file('file')->getRealPath();
        $collection = Excel::toCollection(null, $request->file('file'));

        $rows = $collection[0]; // first sheet

        $imported = 0;
        $skipped = 0;
        foreach ($rows->skip(1) as $row) {
            $name = trim($row[0]);

            if (empty($name) || AccountGroup::where('name', $name)->exists()) {
                $skipped++;
                continue;
            }

            AccountGroup::create(['name' => $name]);
            $imported++;
        }

        return redirect()->route('admin.accounting.groups.index')
            ->with('success', "$imported imported, $skipped skipped.");

    }

}
