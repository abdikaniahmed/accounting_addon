<?php

namespace App\Http\Controllers\Admin\Addons;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Accounting\Contact;
use Brian2694\Toastr\Facades\Toastr;

class VendorController extends Controller
{
    public function index()
    {
        $vendors = Contact::where('type', 'vendor')->latest()->get();
        return view('addons.accounting.vendors_index', compact('vendors'));
    }

    public function create()
    {
        return view('addons.accounting.vendors_create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        Contact::create([
            'type' => 'vendor',
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        Toastr::success(__('Vendor added successfully.'));
        return redirect()->route('admin.accounting.vendors.index');
    }

    public function edit($id)
    {
        $vendor = Contact::where('type', 'vendor')->findOrFail($id);
        return view('addons.accounting.vendors_edit', compact('vendor'));
    }

    public function update(Request $request, $id)
    {
        $vendor = Contact::where('type', 'vendor')->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        $vendor->update($request->only('name', 'email', 'phone', 'address'));

        Toastr::success(__('Vendor updated successfully.'));
        return redirect()->route('admin.accounting.vendors.index');
    }

    public function destroy($id)
    {
        $vendor = Contact::where('type', 'vendor')->findOrFail($id);
        $vendor->delete();

        return response()->json([
            'title'   => __('Deleted!'),
            'message' => __('Vendor deleted successfully.'),
            'status'  => 'success',
            'url'     => null
        ]);
    }
}