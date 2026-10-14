<?php

namespace App\Events;

use App\Models\Product;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired by the InventoryTransactionObserver when a product falls to or below
 * its reorder level. Implementing ShouldDispatchAfterCommit means listeners
 * only run once the surrounding DB::transaction has actually committed - no
 * alert is ever sent for a stock movement that got rolled back.
 */
class LowStockDetected implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(public Product $product)
    {
    }
}
