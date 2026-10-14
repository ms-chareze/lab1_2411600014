<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Part 8 - reconcile system stock with a physical count.
 * Route is protected by the `manage-stock` gate (admin + manager).
 */
class StockAdjustmentController extends Controller
{
    public const REASONS = [
        'Physical count discrepancy',
        'Damaged goods',
        'Theft / loss',
        'System error',
        'Other',
    ];

    public function __construct(private InventoryService $inventory)
    {
    }

    public function create(Request $request)
    {
        return view('adjustments.create', [
            'products'          => Product::orderBy('name')->get(['id', 'name', 'sku']),
            'reasons'           => self::REASONS,
            'selectedProductId' => $request->query('product_id'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id'     => ['required', 'integer', Rule::exists('products', 'id')->whereNull('deleted_at')],
            'physical_count' => ['required', 'integer', 'min:0', 'max:1000000'],
            'reason'         => ['required', Rule::in(self::REASONS)],
            'notes'          => ['nullable', 'string', 'max:1000'],
        ]);

        $product = Product::findOrFail($data['product_id']);

        $transaction = $this->inventory->adjust(
            $product,
            (int) $data['physical_count'],
            $data['reason'],
            $data['notes'] ?? null,
            $request->user()
        );

        return redirect()->route('transactions.show', $transaction)
            ->with('success', sprintf(
                'Stock adjusted for %s: %d -> %d (%s).',
                $product->name,
                $transaction->quantity_before,
                $transaction->quantity_after,
                $transaction->signed_quantity
            ));
    }
}
