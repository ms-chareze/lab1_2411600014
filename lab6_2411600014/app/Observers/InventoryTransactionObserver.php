<?php

namespace App\Observers;

use App\Events\LowStockDetected;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Validation\ValidationException;
use LogicException;

/**
 * Part 2 - Eloquent observer that keeps products.quantity in sync with the ledger.
 *
 * "Should you use created or creating?"  Both, for different jobs:
 *
 *   creating -> runs BEFORE the INSERT. This is the right place to VALIDATE
 *               (reject stock-outs that would go negative) and to SNAPSHOT
 *               quantity_before / quantity_after. Throwing here means the
 *               ledger row is never written.
 *   created  -> runs AFTER the INSERT. Now that the row exists we apply the
 *               change to the product and evaluate the low stock alert.
 *
 * Always create transactions through InventoryService (or inside a
 * DB::transaction) so the lock taken here lasts until both steps finish and
 * everything commits or rolls back together.
 */
class InventoryTransactionObserver
{
    public function creating(InventoryTransaction $transaction): void
    {
        // Row lock: two simultaneous stock-outs cannot both read the same quantity.
        $product = Product::lockForUpdate()->findOrFail($transaction->product_id);

        $newQuantity = $product->quantity + $transaction->quantity;

        // Critical thinking answer - Option A: reject. Negative stock is
        // physically impossible and capping at zero would silently hide a
        // bookkeeping error, so we throw and let the DB transaction roll back.
        if ($newQuantity < 0) {
            throw ValidationException::withMessages([
                'quantity' => sprintf(
                    'Insufficient stock for "%s". Available: %d, requested: %d.',
                    $product->name,
                    $product->quantity,
                    abs($transaction->quantity)
                ),
            ]);
        }

        $transaction->quantity_before = $product->quantity;
        $transaction->quantity_after  = $newQuantity;
    }

    public function created(InventoryTransaction $transaction): void
    {
        $product = Product::withTrashed()->lockForUpdate()->findOrFail($transaction->product_id);

        $this->applyQuantity($product, $transaction->quantity_after);
    }

    /**
     * Ledger rows are an audit trail: they may be created or (by an admin)
     * deleted-with-reversal, but never edited.
     */
    public function updating(InventoryTransaction $transaction): void
    {
        throw new LogicException('Inventory transactions are immutable and cannot be updated.');
    }

    /**
     * Deleting a transaction REVERSES its effect on the stock so the product
     * quantity stays truthful. If reversing would drive stock negative (because
     * the stock was already sold), the delete is refused.
     */
    public function deleting(InventoryTransaction $transaction): void
    {
        $product = Product::withTrashed()->lockForUpdate()->findOrFail($transaction->product_id);

        $newQuantity = $product->quantity - $transaction->quantity;

        if ($newQuantity < 0) {
            throw ValidationException::withMessages([
                'transaction' => sprintf(
                    'This transaction cannot be deleted: reversing it would leave "%s" with negative stock (%d).',
                    $product->name,
                    $newQuantity
                ),
            ]);
        }

        $this->applyQuantity($product, $newQuantity);
    }

    /**
     * Writes the new quantity and handles the low stock alert state.
     *
     * Duplicate prevention (Part 6 "Advanced Consideration"): products carry
     * low_stock_notified_at. We alert only on the transition INTO low stock
     * (flag is null) and clear the flag once the product is restocked above
     * its reorder level, so the next dip alerts again.
     */
    private function applyQuantity(Product $product, int $newQuantity): void
    {
        $product->quantity = $newQuantity;

        $shouldAlert = false;

        if ($product->isLowStock()) {
            if ($product->low_stock_notified_at === null) {
                $product->low_stock_notified_at = now();
                $shouldAlert = true;
            }
        } elseif ($product->low_stock_notified_at !== null) {
            $product->low_stock_notified_at = null;
        }

        $product->save();

        if ($shouldAlert) {
            // The listener is queued, so the request is not slowed down by emails.
            LowStockDetected::dispatch($product);
        }
    }
}
