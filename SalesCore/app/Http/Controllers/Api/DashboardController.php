<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $completedSalesQuery = Sale::where('status', 'completed')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth]);

        $canceledSalesQuery = Sale::where('status', 'canceled')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth]);

        $completedSalesCount = (clone $completedSalesQuery)->count();
        $totalSold = (clone $completedSalesQuery)->sum('total');
        $canceledSalesCount = (clone $canceledSalesQuery)->count();

        $averageTicket = $completedSalesCount > 0
            ? $totalSold / $completedSalesCount : 0;

        $activeProductsCount = Product::where('active', true)->count();
        $activeCustomersCount = Customer::where('active', true)->count();
        $activeSuppliersCount = Supplier::where('active', true)->count();

        $lowStockProducts = Product::where('active', true)
            ->where('minimum_stock', '>', 0)
            ->whereColumn('stock_quantity', '<=', 'minimum_stock')
            ->orderBy('stock_quantity')
            ->limit(5)
            ->get();

        $latestSales = Sale::with('customer')
            ->latest()
            ->limit(5)
            ->get();

        $latestStockMovements = StockMovement::with('product')
            ->latest()
            ->limit(5)
            ->get();

        return response()->json([
            'month_summary' => [
                'total_sold' => $totalSold,
                'completed_sales_count' => $completedSalesCount,
                'canceled_sales_count' => $canceledSalesCount,
                'average_ticket' => $averageTicket,
            ],
            'registrations' => [
                'active_products_count' => $activeProductsCount,
                'active_customers_count' => $activeCustomersCount,
                'active_suppliers_count' => $activeSuppliersCount,
        ],
        'low_stock_products' => $lowStockProducts,
        'latest_sales' => $latestSales,
        'latest_stock_movements' => $latestStockMovements,
        ]);
    }
}
