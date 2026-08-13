<?php

namespace App\Http\Controllers;

use App\Models\StockLedger;
use App\Models\Medicine;
use Illuminate\Http\Request;

class StockLedgerController extends Controller
{
    public function index(Request $request)
    {
        $isPremium = feature_enabled('stock_ledger');
        $medicines = Medicine::where('is_active', true)->pluck('name', 'id');

        // If feature is not enabled, don't query ledger data — show gated preview
        if (!$isPremium) {
            $ledgers = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
            return view('reports.ledger', compact('ledgers', 'medicines', 'isPremium'));
        }

        $query = StockLedger::with(['medicine', 'batch', 'user'])->latest();

        if ($request->filled('medicine_id')) {
            $query->where('medicine_id', $request->input('medicine_id'));
        }

        if ($request->filled('movement_type')) {
            $query->where('movement_type', $request->input('movement_type'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('medicine', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('generic_name', 'like', "%{$search}%");
            });
        }

        $ledgers = $query->paginate(20);

        return view('reports.ledger', compact('ledgers', 'medicines', 'isPremium'));
    }
}
