<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Medicine;
use App\Models\Sale;
use App\Models\SaleReturn;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function sales(Request $request)
    {
        $from = $request->get('from', Carbon::today()->startOfMonth()->format('Y-m-d'));
        $to = $request->get('to', Carbon::today()->format('Y-m-d'));

        $sales = Sale::with(['items.medicine', 'customer', 'user'])
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->latest()
            ->get();

        $grossRevenue = (float) $sales->sum('total');
        $totalRefunds = (float) SaleReturn::whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->sum('refund_amount');
        $totalRevenue = max(0, $grossRevenue - $totalRefunds);
        $totalTransactions = $sales->count();
        $averagePerTransaction = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;

        $export = $request->get('export');
        if ($export === 'excel' || $export === 'xls') {
            if (!feature_enabled('advanced_reports')) {
                return redirect()->back()->with('error', 'Excel export is a Premium feature. Activate your Premium license under Settings → License & Edition to unlock exports.');
            }
            return $this->exportSalesExcel($sales, $from, $to, $grossRevenue, $totalRefunds, $totalRevenue, $totalTransactions);
        }

        return view('reports.sales', compact('sales', 'from', 'to', 'grossRevenue', 'totalRefunds', 'totalRevenue', 'totalTransactions', 'averagePerTransaction'));
    }

    private function exportSalesExcel($sales, $from, $to, $grossRevenue, $totalRefunds, $totalRevenue, $totalTransactions)
    {
        $pharmacy = setting('pharmacy_name', 'PharmCare Pharmacy');
        $currency = setting('currency_symbol', 'UGX');
        $filename = 'sales_report_' . date('Y-m-d_H-i') . '.xls';

        $html = view('reports.exports.sales_excel', compact(
            'sales', 'from', 'to', 'grossRevenue', 'totalRefunds', 'totalRevenue', 'totalTransactions', 'pharmacy', 'currency'
        ))->render();

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    public function inventory(Request $request)
    {
        $categoryId = $request->get('category_id');
        $stockStatus = $request->get('stock_status');
        $search = $request->get('search');

        $query = Medicine::with(['category', 'batches' => function ($q) {
            $q->where('quantity', '>', 0)
              ->where('is_active', true)
              ->whereDate('expiry_date', '>', now());
        }]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('generic_name', 'like', "%{$search}%")
                  ->orWhere('manufacturer', 'like', "%{$search}%");
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $medicines = $query->get()->map(function ($medicine) {
            $medicine->total_stock = (int)$medicine->batches->sum('quantity');
            $medicine->stock_cost_value = (float)$medicine->batches->sum(fn ($b) => $b->quantity * $b->purchase_price);
            $medicine->stock_retail_value = (float)$medicine->batches->sum(fn ($b) => $b->quantity * $b->selling_price);
            $medicine->avg_purchase_price = $medicine->batches->count() > 0 ? (float)$medicine->batches->avg('purchase_price') : 0;
            $medicine->avg_selling_price = $medicine->batches->count() > 0 ? (float)$medicine->batches->avg('selling_price') : 0;
            
            if ($medicine->total_stock <= 0) {
                $medicine->status_label = 'Out of Stock';
                $medicine->status_badge = 'bg-rose-100 text-rose-800 border border-rose-200';
            } elseif ($medicine->total_stock <= $medicine->reorder_level) {
                $medicine->status_label = 'Low Stock';
                $medicine->status_badge = 'bg-amber-100 text-amber-800 border border-amber-200';
            } else {
                $medicine->status_label = 'In Stock';
                $medicine->status_badge = 'bg-emerald-100 text-emerald-800 border border-emerald-200';
            }

            return $medicine;
        });

        if ($stockStatus === 'low_stock') {
            $medicines = $medicines->filter(fn ($m) => $m->total_stock > 0 && $m->total_stock <= $m->reorder_level);
        } elseif ($stockStatus === 'out_of_stock') {
            $medicines = $medicines->filter(fn ($m) => $m->total_stock <= 0);
        } elseif ($stockStatus === 'in_stock') {
            $medicines = $medicines->filter(fn ($m) => $m->total_stock > $m->reorder_level);
        }

        $medicines = $medicines->values();

        $totalMedicines = $medicines->count();
        $totalStockQty = $medicines->sum('total_stock');
        $totalCostValue = $medicines->sum('stock_cost_value');
        $totalRetailValue = $medicines->sum('stock_retail_value');

        $allMeds = Medicine::where('is_active', true)->with(['batches' => function($q) {
            $q->where('quantity', '>', 0)->where('is_active', true)->whereDate('expiry_date', '>', now());
        }])->get();

        $lowStockCount = $allMeds->filter(function($m) {
            $st = $m->batches->sum('quantity');
            return $st > 0 && $st <= $m->reorder_level;
        })->count();

        $outOfStockCount = $allMeds->filter(function($m) {
            return $m->batches->sum('quantity') <= 0;
        })->count();

        $categories = \App\Models\Category::pluck('name', 'id');

        $export = $request->get('export');
        if ($export === 'excel' || $export === 'xls') {
            if (!feature_enabled('advanced_inventory')) {
                return redirect()->back()->with('error', 'Excel export is a Premium feature. Activate your Premium license under Settings → License & Edition to unlock exports.');
            }
            return $this->exportInventoryExcel($medicines, $totalMedicines, $totalStockQty, $totalCostValue, $totalRetailValue);
        }

        return view('reports.inventory', compact(
            'medicines', 
            'totalMedicines', 
            'totalStockQty', 
            'totalCostValue', 
            'totalRetailValue',
            'lowStockCount',
            'outOfStockCount',
            'categories',
            'categoryId',
            'stockStatus',
            'search'
        ));
    }

    private function exportInventoryExcel($medicines, $totalMedicines, $totalStockQty, $totalCostValue, $totalRetailValue)
    {
        $pharmacy = setting('pharmacy_name', 'PharmCare Pharmacy');
        $currency = setting('currency_symbol', 'UGX');
        $filename = 'inventory_report_' . date('Y-m-d_H-i') . '.xls';

        $html = view('reports.exports.inventory_excel', compact(
            'medicines', 'totalMedicines', 'totalStockQty', 'totalCostValue', 'totalRetailValue', 'pharmacy', 'currency'
        ))->render();

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    public function expiry(Request $request)
    {
        $from = $request->get('from', Carbon::today()->format('Y-m-d'));
        $to = $request->get('to', Carbon::today()->addMonths(3)->format('Y-m-d'));

        $batches = Batch::with('medicine', 'supplier')
            ->where('expiry_date', '>=', $from)
            ->where('expiry_date', '<=', $to)
            ->where('quantity', '>', 0)
            ->orderBy('expiry_date')
            ->get();

        $totalBatches = $batches->count();
        $totalQuantity = $batches->sum('quantity');
        $totalValue = $batches->sum(fn ($b) => $b->purchase_price * $b->quantity);

        $export = $request->get('export');
        if ($export === 'excel' || $export === 'xls') {
            return $this->exportExpiryExcel($batches, $from, $to, $totalBatches, $totalQuantity, $totalValue);
        }

        return view('reports.expiry', compact('batches', 'from', 'to', 'totalBatches', 'totalQuantity', 'totalValue'));
    }

    private function exportExpiryExcel($batches, $from, $to, $totalBatches, $totalQuantity, $totalValue)
    {
        $pharmacy = setting('pharmacy_name', 'PharmCare Pharmacy');
        $currency = setting('currency_symbol', 'UGX');
        $filename = 'expiry_report_' . date('Y-m-d_H-i') . '.xls';

        $html = view('reports.exports.expiry_excel', compact(
            'batches', 'from', 'to', 'totalBatches', 'totalQuantity', 'totalValue', 'pharmacy', 'currency'
        ))->render();

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
