<?php

namespace App\Listeners;

use App\Events\LowStockDetected;
use App\Models\Product;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

/**
 * Part 6 - runs on the queue (QUEUE_CONNECTION=database), so sending email
 * never slows the request that recorded the stock movement.
 *
 * Laravel 11/12 auto-discovers this listener from the type-hint on handle(),
 * so it needs no manual registration.
 *
 * Remember to run a worker:  php artisan queue:listen   (or `composer dev`).
 */
class SendLowStockNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /** If the product was removed before the job ran, drop the job quietly. */
    public bool $deleteWhenMissingModels = true;

    public function handle(LowStockDetected $event): void
    {
        // Re-read the product: it may have been restocked while the job waited in the queue.
        $product = Product::find($event->product->id);

        if (! $product || ! $product->isLowStock()) {
            return;
        }

        $recipients = User::managers()->get();

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send($recipients, new LowStockNotification($product));
    }
}
