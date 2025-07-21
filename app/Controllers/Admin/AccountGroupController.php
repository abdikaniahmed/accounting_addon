<?php

namespace App\Http\Controllers\Admin\Addons;

use App\Http\Controllers\Controller;
use App\Models\Accounting\AccountGroup;
use Illuminate\Http\Request;

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
        $group = AccountGroup::findOrFail($id);
        $group->delete();
        return redirect()->route('admin.accounting.groups.index')->with('success', 'Group deleted.');
    }
}
