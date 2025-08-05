<?php

namespace App\Http\Controllers\Admin\Addons;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Accounting\Contact;
use Brian2694\Toastr\Facades\Toastr;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Contact::where('type', 'customer')->latest()->get();
        return view('addons.accounting.customers_index', compact('customers'));
    }

    public function create()
    {
        return view('addons.accounting.customers_create');
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
            'type' => 'customer',
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        Toastr::success(__('Customer added successfully.'));
        return redirect()->route('admin.accounting.customers.index');
    }

    public function edit($id)
    {
        $customer = Contact::where('type', 'customer')->findOrFail($id);
        return view('addons.accounting.customers_edit', compact('customer'));
    }

    public function update(Request $request, $id)
    {
        $customer = Contact::where('type', 'customer')->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        $customer->update($request->only('name', 'email', 'phone', 'address'));

        Toastr::success(__('Customer updated successfully.'));
        return redirect()->route('admin.accounting.customers.index');
    }

    public function destroy($id)
    {
        $customer = Contact::where('type', 'customer')->findOrFail($id);
        $customer->delete();

        return response()->json([
            'title'   => __('Deleted!'),
            'message' => __('Customer deleted successfully.'),
            'status'  => 'success',
            'url'     => null // Optional: add URL if you want to reload/redirect
        ]);
    }
}