<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function __construct(private InventoryService $inventory)
    {
    }

    public function index(Request $request)
    {
        $products = Product::query()
            ->when($request->query('q'), function ($query, $term) {
                $query->where(fn ($w) => $w->where('name', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%"));
            })
            ->inCategory($request->query('category'))
            ->when($request->query('status'), function ($query, $status) {
                return match ($status) {
                    'in'    => $query->whereColumn('quantity', '>', 'reorder_level'),
                    'low'   => $query->lowStock()->where('quantity', '>', 0),
                    'out'   => $query->outOfStock(),
                    default => $query,
                };
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $categories = Product::query()->distinct()->orderBy('category')->pluck('category');

        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        return view('products.form', [
            'categories' => $this->categories(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku'              => ['required', 'string', 'max:50', Rule::unique('products', 'sku')],
            'name'             => ['required', 'string', 'max:255'],
            'category'         => ['required', 'string', 'max:100'],
            'description'      => ['nullable', 'string', 'max:2000'],
            'price'            => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'initial_quantity' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'reorder_level'    => ['required', 'integer', 'min:0', 'max:1000000'],
        ]);

        $initial = (int) ($validated['initial_quantity'] ?? 0);

        $product = DB::transaction(function () use ($validated, $initial, $request) {
            $product = Product::create([
                'sku'           => $validated['sku'],
                'name'          => $validated['name'],
                'category'      => $validated['category'],
                'description'   => $validated['description'] ?? null,
                'price'         => $validated['price'],
                'reorder_level' => $validated['reorder_level'],
                'quantity'      => 0, // stock only ever moves through the ledger
            ]);

            if ($initial > 0) {
                $this->inventory->record($product, InventoryTransaction::TYPE_IN, $initial, $request->user(), [
                    'reference_number' => 'OPENING',
                    'notes'            => 'Opening balance when the product was created.',
                ]);
            }

            return $product;
        });

        return redirect()->route('products.show', $product)
            ->with('success', 'Product created successfully.');
    }

    public function show(Request $request, Product $product)
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to'   => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        // Part 9: history with running balance (quantity_after is the balance snapshot).
        $history = $product->transactions()
            ->with('user')
            ->filter($filters)
            ->recent()
            ->paginate(15)
            ->withQueryString();

        return view('products.show', compact('product', 'history'));
    }

    public function edit(Product $product)
    {
        return view('products.form', [
            'product'    => $product,
            'categories' => $this->categories(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        // Quantity is intentionally not editable here: use a transaction or an adjustment.
        $validated = $request->validate([
            'sku'           => ['required', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($product->id)],
            'name'          => ['required', 'string', 'max:255'],
            'category'      => ['required', 'string', 'max:100'],
            'description'   => ['nullable', 'string', 'max:2000'],
            'price'         => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'reorder_level' => ['required', 'integer', 'min:0', 'max:1000000'],
        ]);

        $product->update($validated);

        // A changed reorder level can move the product in or out of "low stock"; keep the alert flag honest.
        if (! $product->isLowStock() && $product->low_stock_notified_at !== null) {
            $product->forceFill(['low_stock_notified_at' => null])->save();
        }

        return redirect()->route('products.show', $product)
            ->with('success', 'Product updated successfully.');
    }

    /** Admin only (route middleware). Soft delete: ledger history is preserved. */
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', "Product \"{$product->name}\" deleted. Its transaction history is kept.");
    }

    private function categories()
    {
        return Product::query()->distinct()->orderBy('category')->pluck('category');
    }
}
