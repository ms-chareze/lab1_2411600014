<?php

namespace App\Services;

use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * One source of truth for dashboard numbers. The web DashboardController uses
 * it for the first page render and the JSON API uses it for the 30-second
 * AJAX refresh, so the two can never disagree.
 */
class DashboardService
{
    public function stats(): array
    {
        $today = today();

        return [
            'total_products'     => Product::count(),
            'total_units'        => (int) Product::sum('quantity'),
            'inventory_value'    => round((float) Product::query()
                ->selectRaw('COALESCE(SUM(quantity * price), 0) as total')
                ->value('total'), 2),
            'low_stock_count'    => Product::lowStock()->count(),
            'out_of_stock_count' => Product::outOfStock()->count(),
            'transactions_today' => InventoryTransaction::whereDate('created_at', $today)->count(),
            'stock_in_today'     => (int) InventoryTransaction::stockIn()->whereDate('created_at', $today)->sum('quantity'),
            'stock_out_today'    => abs((int) InventoryTransaction::stockOut()->whereDate('created_at', $today)->sum('quantity')),
        ];
    }

    /** Most urgent first: emptiest shelves at the top. */
    public function lowStockProducts(int $limit = 10): Collection
    {
        return Product::lowStock()
            ->orderBy('quantity')
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    public function recentTransactions(int $limit = 10): Collection
    {
        return InventoryTransaction::with(['product', 'user'])
            ->recent()
            ->limit($limit)
            ->get();
    }
}
