<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::paginate(10);
        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:255',
            'address' => 'nullable|string',
            'is_active' => 'nullable',
        ]);

        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;

        $customer = Customer::create($validated);

        $this->logActivity('customer_created', 'Customer', $customer->id, "Customer '{$customer->name}' created.");

        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer)
    {
        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:255',
            'address' => 'nullable|string',
            'is_active' => 'nullable',
        ]);

        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : $customer->is_active;

        $customer->update($validated);

        $this->logActivity('customer_updated', 'Customer', $customer->id, "Customer '{$customer->name}' updated.");

        return redirect()->route('customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    public function toggleStatus(Customer $customer)
    {
        $customer->is_active = !$customer->is_active;
        $customer->save();

        $statusStr = $customer->is_active ? 'activated' : 'deactivated';
        $this->logActivity('customer_status_changed', 'Customer', $customer->id, "Customer '{$customer->name}' {$statusStr}.");

        return redirect()->back()->with('success', "Customer '{$customer->name}' {$statusStr} successfully.");
    }

    public function destroy(Customer $customer)
    {
        if ($customer->sales()->exists() || $customer->prescriptions()->exists()) {
            return redirect()->route('customers.index')
                ->with('error', 'Cannot delete customer because they have sales or prescriptions.');
        }

        $this->logActivity('customer_deleted', 'Customer', $customer->id, "Customer '{$customer->name}' deleted.");
        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }
}
