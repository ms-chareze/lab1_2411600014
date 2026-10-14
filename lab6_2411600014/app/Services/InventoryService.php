<?php

namespace App\Services;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

/**
 * Single entry point for moving stock. Every controller (and the seeder) goes
 * through here so each movement is wrapped in a database transaction:
 *
 *     ledger row INSERT  +  products.quantity UPDATE   -> all or nothing
 *
 * If anything throws inside DB::transaction(), Laravel issues ROLLBACK and
 * rethrows the exception - neither the transaction row nor the quantity change
 * is kept.
 */
class InventoryService
{
    /**
     * Record a stock movement.
     *
     * @param int   $quantity A positive number for stock_in / stock_out (the
     *                        sign is applied here). For adjustments pass the
     *                        signed difference.
     * @param array{reference_number?:?string, reason?:?string, notes?:?string} $details
     *
     * @throws ValidationException when stock would go negative
     */
    public function record(Product $product, string $type, int $quantity, ?User $user = null, array $details = []): InventoryTransaction
    {
        $signed = match ($type) {
            InventoryTransaction::TYPE_IN         => abs($quantity),
            InventoryTransaction::TYPE_OUT        => -abs($quantity),
            InventoryTransaction::TYPE_ADJUSTMENT => $quantity,
            default => throw new InvalidArgumentException("Unknown transaction type [{$type}]."),
        };

        if ($signed === 0) {
            throw ValidationException::withMessages([
                'quantity' => 'The quantity must not be zero.',
            ]);
        }

        return DB::transaction(function () use ($product, $type, $signed, $user, $details) {
            // The observer validates stock, snapshots before/after and updates the product.
            return InventoryTransaction::create([
                'product_id'       => $product->id,
                'user_id'          => $user?->id,
                'type'             => $type,
                'quantity'         => $signed,
                'reference_number' => $details['reference_number'] ?? null,
                'reason'           => $details['reason'] ?? null,
                'notes'            => $details['notes'] ?? null,
            ]);
        });
    }

    /**
     * Part 8 - reconcile the system quantity with a physical count.
     * difference = physical - system; the adjustment transaction carries it.
     *
     * @throws ValidationException when there is nothing to adjust
     */
    public function adjust(Product $product, int $physicalCount, string $reason, ?string $notes, ?User $user): InventoryTransaction
    {
        return DB::transaction(function () use ($product, $physicalCount, $reason, $notes, $user) {
            // Lock + re-read so the difference is computed against the live quantity.
            $locked = Product::lockForUpdate()->findOrFail($product->id);

            $difference = $physicalCount - $locked->quantity;

            if ($difference === 0) {
                throw ValidationException::withMessages([
                    'physical_count' => "The physical count matches the system quantity ({$locked->quantity}). No adjustment is needed.",
                ]);
            }

            return $this->record($locked, InventoryTransaction::TYPE_ADJUSTMENT, $difference, $user, [
                'reference_number' => 'ADJ-' . now()->format('Ymd-His'),
                'reason'           => $reason,
                'notes'            => $notes,
            ]);
        });
    }
}
