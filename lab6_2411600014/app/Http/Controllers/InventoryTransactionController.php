<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInventoryTransactionRequest;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryTransactionController extends Controller
{
    public function __construct(private InventoryService $inventory)
    {
    }

    public function index(Request $request)
    {
        $filters = $request->validate([
            'product_id' => ['nullable', 'integer'],
            'type'       => ['nullable', 'in:' . implode(',', array_keys(InventoryTransaction::types()))],
            'from'       => ['nullable', 'date'],
            'to'         => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $transactions = InventoryTransaction::with(['product', 'user']) // eager load: no N+1
            ->filter($filters)
            ->recent()
            ->paginate(20)
            ->withQueryString();

        return view('transactions.index', [
            'transactions' => $transactions,
            'products'     => Product::orderBy('name')->get(['id', 'name', 'sku']),
            'types'        => InventoryTransaction::types(),
        ]);
    }

    public function create(Request $request)
    {
        $products = Product::orderBy('name')->get(['id', 'name', 'sku', 'quantity', 'reorder_level']);

        return view('transactions.create', [
            'products'          => $products,
            'types'             => InventoryTransaction::manualTypes(),
            'selectedProductId' => $request->query('product_id'),
        ]);
    }

    public function store(StoreInventoryTransactionRequest $request)
    {
        $data    = $request->validated();
        $product = Product::findOrFail($data['product_id']);

        // The service wraps the insert in DB::transaction; the observer updates the stock.
        $transaction = $this->inventory->record(
            $product,
            $data['type'],
            (int) $data['quantity'],
            $request->user(),
            [
                'reference_number' => $data['reference_number'] ?? null,
                'notes'            => $data['notes'] ?? null,
            ]
        );

        return redirect()->route('transactions.index')
            ->with('success', sprintf(
                '%s recorded: %s %d (stock now %d).',
                $transaction->type_label,
                $product->name,
                abs($transaction->quantity),
                $transaction->quantity_after
            ));
    }

    public function show(InventoryTransaction $transaction)
    {
        $transaction->load(['product', 'user']);

        return view('transactions.show', compact('transaction'));
    }

    /**
     * Admin only (route middleware).
     *
     * Design decision: deleting a transaction REVERSES its stock effect (done by
     * the observer's deleting hook) so product quantities stay truthful. If the
     * reversal would make stock negative, the delete is refused.
     */
    public function destroy(InventoryTransaction $transaction)
    {
        try {
            DB::transaction(fn () => $transaction->delete());
        } catch (ValidationException $e) {
            return redirect()->back()->with('error', collect($e->errors())->flatten()->first());
        }

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction deleted and its stock effect was reversed.');
    }
}
