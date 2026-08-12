<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Medicine;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrescriptionController extends Controller
{
    public function index()
    {
        $prescriptions = Prescription::with('customer', 'user')->latest()->paginate(10);
        return view('prescriptions.index', compact('prescriptions'));
    }

    public function create()
    {
        $customers = Customer::where('is_active', true)->pluck('name', 'id');
        $medicines = Medicine::where('is_active', true)->pluck('name', 'id');
        return view('prescriptions.create', compact('customers', 'medicines'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'doctor_name' => 'required',
            'hospital' => 'nullable',
            'diagnosis' => 'nullable',
            'notes' => 'nullable',
            'items' => 'required|array',
            'items.*.medicine_id' => 'required|exists:medicines,id',
            'items.*.dosage' => 'required',
            'items.*.duration' => 'required',
        ]);

        $prescription = Prescription::create([
            'customer_id' => $validated['customer_id'],
            'doctor_name' => $validated['doctor_name'],
            'hospital' => $validated['hospital'] ?? null,
            'diagnosis' => $validated['diagnosis'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'user_id' => Auth::id(),
        ]);

        foreach ($validated['items'] as $item) {
            PrescriptionItem::create([
                'prescription_id' => $prescription->id,
                'medicine_id' => $item['medicine_id'],
                'dosage' => $item['dosage'],
                'duration' => $item['duration'],
                'notes' => $item['notes'] ?? null,
            ]);
        }

        $this->logActivity('prescription_created', 'Prescription', $prescription->id, "Prescription for {$validated['doctor_name']} created.");

        return redirect()->route('prescriptions.index')
            ->with('success', 'Prescription created successfully.');
    }

    public function show(Prescription $prescription)
    {
        $prescription->load('items.medicine', 'customer', 'user');
        return view('prescriptions.show', compact('prescription'));
    }

    public function edit(Prescription $prescription)
    {
        $customers = Customer::where('is_active', true)->pluck('name', 'id');
        $medicines = Medicine::where('is_active', true)->pluck('name', 'id');
        $prescription->load('items');
        return view('prescriptions.edit', compact('prescription', 'customers', 'medicines'));
    }

    public function update(Request $request, Prescription $prescription)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'doctor_name' => 'required',
            'hospital' => 'nullable',
            'diagnosis' => 'nullable',
            'notes' => 'nullable',
            'items' => 'required|array',
            'items.*.medicine_id' => 'required|exists:medicines,id',
            'items.*.dosage' => 'required',
            'items.*.duration' => 'required',
        ]);

        $prescription->update([
            'customer_id' => $validated['customer_id'],
            'doctor_name' => $validated['doctor_name'],
            'hospital' => $validated['hospital'] ?? null,
            'diagnosis' => $validated['diagnosis'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        $prescription->items()->delete();

        foreach ($validated['items'] as $item) {
            PrescriptionItem::create([
                'prescription_id' => $prescription->id,
                'medicine_id' => $item['medicine_id'],
                'dosage' => $item['dosage'],
                'duration' => $item['duration'],
                'notes' => $item['notes'] ?? null,
            ]);
        }

        $this->logActivity('prescription_updated', 'Prescription', $prescription->id, "Prescription #{$prescription->id} updated.");

        return redirect()->route('prescriptions.index')
            ->with('success', 'Prescription updated successfully.');
    }

    public function destroy(Prescription $prescription)
    {
        $prescription->items()->delete();
        $prescription->delete();

        $this->logActivity('prescription_deleted', 'Prescription', $prescription->id, "Prescription #{$prescription->id} deleted.");

        return redirect()->route('prescriptions.index')
            ->with('success', 'Prescription deleted successfully.');
    }
}
