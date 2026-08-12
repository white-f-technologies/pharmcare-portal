<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Batch;
use App\Models\Customer;
use App\Models\Medicine;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalMedicines = Medicine::count();

        $lowStock = Medicine::where('is_active', true)
            ->whereRaw('(SELECT COALESCE(SUM(quantity), 0) FROM batches WHERE batches.medicine_id = medicines.id AND batches.quantity > 0 AND batches.is_active = 1) <= reorder_level')
            ->count();

        $todayGrossSales = Sale::whereDate('created_at', Carbon::today())->sum('total');
        $todayRefunds = \App\Models\SaleReturn::whereDate('created_at', Carbon::today())->sum('refund_amount');
        $todaySalesTotal = max(0, $todayGrossSales - $todayRefunds);

        // Today's Daily Net Profit Calculation
        $todaySaleItems = \App\Models\SaleItem::whereHas('sale', function ($q) {
            $q->whereDate('created_at', Carbon::today());
        })->with('batch')->get();

        $todaySaleReturns = \App\Models\SaleReturn::whereDate('created_at', Carbon::today())->with('batch')->get();

        $todayRealizedCogs = (float) $todaySaleItems->sum(function ($item) {
            $purchasePrice = $item->batch ? (float)$item->batch->purchase_price : 0;
            return (float)$item->quantity * $purchasePrice;
        });

        $todayRefundedCogs = (float) $todaySaleReturns->sum(function ($ret) {
            $purchasePrice = $ret->batch ? (float)$ret->batch->purchase_price : 0;
            return (float)$ret->returned_base_quantity * $purchasePrice;
        });

        $todayNetCogs = max(0, $todayRealizedCogs - $todayRefundedCogs);
        $todayExpenses = (float) \App\Models\Expense::whereDate('expense_date', Carbon::today())->sum('amount');
        $todayProfitTotal = max(0, ($todaySalesTotal - $todayNetCogs) - $todayExpenses);

        $totalSales = Sale::count();
        $totalPurchases = Purchase::count();
        $totalCustomers = Customer::count();

        $recentSales = Sale::with('customer')
            ->latest()
            ->take(10)
            ->get();

        $recentPurchases = Purchase::with('supplier')
            ->latest()
            ->take(10)
            ->get();

        $recentActivities = ActivityLog::with('user')
            ->latest()
            ->take(10)
            ->get();

        $expiringSoonBatches = Batch::with('medicine')
            ->where('expiry_date', '>=', Carbon::today())
            ->where('expiry_date', '<=', Carbon::today()->addDays(30))
            ->where('quantity', '>', 0)
            ->orderBy('expiry_date')
            ->take(10)
            ->get();

        $chartData = $this->getChartData();

        return view('dashboard', array_merge(
            compact(
                'totalMedicines',
                'lowStock',
                'todaySalesTotal',
                'todayProfitTotal',
                'totalSales',
                'totalPurchases',
                'totalCustomers',
                'recentSales',
                'recentPurchases',
                'recentActivities',
                'expiringSoonBatches'
            ),
            $chartData
        ));
    }

    public function liveData()
    {
        $totalMedicines = Medicine::count();

        $lowStock = Medicine::where('is_active', true)
            ->whereRaw('(SELECT COALESCE(SUM(quantity), 0) FROM batches WHERE batches.medicine_id = medicines.id AND batches.quantity > 0 AND batches.is_active = 1) <= reorder_level')
            ->count();

        $todayGrossSales = Sale::whereDate('created_at', Carbon::today())->sum('total');
        $todayRefunds = \App\Models\SaleReturn::whereDate('created_at', Carbon::today())->sum('refund_amount');
        $todaySalesTotal = max(0, $todayGrossSales - $todayRefunds);

        // Today's Daily Net Profit Calculation
        $todaySaleItems = \App\Models\SaleItem::whereHas('sale', function ($q) {
            $q->whereDate('created_at', Carbon::today());
        })->with('batch')->get();

        $todaySaleReturns = \App\Models\SaleReturn::whereDate('created_at', Carbon::today())->with('batch')->get();

        $todayRealizedCogs = (float) $todaySaleItems->sum(function ($item) {
            $purchasePrice = $item->batch ? (float)$item->batch->purchase_price : 0;
            return (float)$item->quantity * $purchasePrice;
        });

        $todayRefundedCogs = (float) $todaySaleReturns->sum(function ($ret) {
            $purchasePrice = $ret->batch ? (float)$ret->batch->purchase_price : 0;
            return (float)$ret->returned_base_quantity * $purchasePrice;
        });

        $todayNetCogs = max(0, $todayRealizedCogs - $todayRefundedCogs);
        $todayExpenses = (float) \App\Models\Expense::whereDate('expense_date', Carbon::today())->sum('amount');
        $todayProfitTotal = max(0, ($todaySalesTotal - $todayNetCogs) - $todayExpenses);

        $totalSales = Sale::count();
        $totalPurchases = Purchase::count();
        $totalCustomers = Customer::count();

        $recentSales = Sale::with('customer')
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($sale) => [
                'id' => $sale->id,
                'invoice_no' => $sale->invoice_no,
                'customer' => $sale->customer?->name ?? 'Walk-in',
                'total' => format_price($sale->total),
                'payment_method' => ucfirst($sale->payment_method),
                'payment_status' => ucfirst($sale->payment_status),
                'date' => $sale->created_at->format('Y-m-d H:i'),
            ]);

        $recentPurchases = Purchase::with('supplier')
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'invoice_no' => $p->invoice_no,
                'supplier' => $p->supplier?->name ?? '-',
                'total' => format_price($p->total),
                'status' => ucfirst($p->status),
                'date' => $p->created_at->format('Y-m-d H:i'),
            ]);

        $recentActivities = ActivityLog::with('user')
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($log) => [
                'id' => $log->id,
                'user' => $log->user?->name ?? 'System',
                'action' => $log->action,
                'description' => $log->description,
                'time' => $log->created_at->diffForHumans(),
            ]);

        $chartData = $this->getChartData();

        return response()->json(array_merge([
            'totalMedicines' => $totalMedicines,
            'lowStock' => $lowStock,
            'todaySalesTotal' => format_price($todaySalesTotal),
            'todayProfitTotal' => format_price($todayProfitTotal),
            'totalSales' => $totalSales,
            'totalPurchases' => $totalPurchases,
            'totalCustomers' => $totalCustomers,
            'recentSales' => $recentSales,
            'recentPurchases' => $recentPurchases,
            'recentActivities' => $recentActivities,
            'updatedAt' => now()->toIso8601String(),
        ], $chartData));
    }

    private function getChartData()
    {
        $last7Days = collect(range(6, 0))->map(function ($days) {
            return Carbon::today()->subDays($days)->format('Y-m-d');
        });

        // Weekly Sales (Net of Returns)
        $salesData = Sale::selectRaw('DATE(created_at) as date, SUM(total) as total')
            ->where('created_at', '>=', Carbon::today()->subDays(6)->startOfDay())
            ->groupBy('date')
            ->get()
            ->pluck('total', 'date');

        $returnsData = \App\Models\SaleReturn::selectRaw('DATE(created_at) as date, SUM(refund_amount) as total')
            ->where('created_at', '>=', Carbon::today()->subDays(6)->startOfDay())
            ->groupBy('date')
            ->get()
            ->pluck('total', 'date');

        $chartSales = $last7Days->map(function ($date) use ($salesData, $returnsData) {
            $gross = (float) ($salesData[$date] ?? 0);
            $refund = (float) ($returnsData[$date] ?? 0);
            return max(0, $gross - $refund);
        })->toArray();

        // Weekly Purchases
        $purchasesData = Purchase::selectRaw('DATE(created_at) as date, SUM(total) as total')
            ->where('created_at', '>=', Carbon::today()->subDays(6)->startOfDay())
            ->groupBy('date')
            ->get()
            ->pluck('total', 'date');

        $chartPurchases = $last7Days->map(fn($date) => (float) ($purchasesData[$date] ?? 0))->toArray();

        $chartLabels = $last7Days->map(fn($date) => Carbon::parse($date)->format('M d'))->toArray();

        // Category Distribution
        $categories = Category::withCount('medicines')
            ->orderByDesc('medicines_count')
            ->get();

        $topCategories = $categories->take(5);
        $otherCount = $categories->slice(5)->sum('medicines_count');

        $categoryLabels = $topCategories->pluck('name')->toArray();
        $categoryCounts = $topCategories->pluck('medicines_count')->toArray();

        if ($otherCount > 0) {
            $categoryLabels[] = 'Other';
            $categoryCounts[] = $otherCount;
        }

        return [
            'chartLabels' => $chartLabels,
            'chartSales' => $chartSales,
            'chartPurchases' => $chartPurchases,
            'categoryLabels' => $categoryLabels,
            'categoryCounts' => $categoryCounts,
        ];
    }
}
