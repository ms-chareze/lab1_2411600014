<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Part 6 Step 1 - what a manager needs to act: product name + SKU, current
 * quantity, reorder level and the suggested action.
 *
 * Delivered over two channels:
 *   database -> powers the bell icon in the navbar
 *   mail     -> MAIL_MAILER=log writes the email to storage/logs/laravel.log
 *
 * It is NOT queued itself because it is already sent from a queued listener
 * (SendLowStockNotification); queuing twice would add nothing.
 */
class LowStockNotification extends Notification
{
    public function __construct(public Product $product)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $p = $this->product;

        return (new MailMessage)
            ->subject("Low Stock Alert: {$p->name} ({$p->sku})")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$p->name} (SKU {$p->sku}) has fallen to or below its reorder level.")
            ->line("Current quantity: {$p->quantity}")
            ->line("Reorder level: {$p->reorder_level}")
            ->line('Suggested action: reorder this product from your supplier.')
            ->action('View Product', route('products.show', $p));
    }

    /** Stored as JSON in notifications.data and read back by the navbar bell. */
    public function toArray(object $notifiable): array
    {
        $p = $this->product;

        return [
            'product_id'    => $p->id,
            'product_name'  => $p->name,
            'sku'           => $p->sku,
            'quantity'      => $p->quantity,
            'reorder_level' => $p->reorder_level,
            'message'       => "{$p->name} is low on stock ({$p->quantity} left, reorder at {$p->reorder_level}).",
            'action'        => 'Reorder this product',
            'url'           => route('products.show', $p),
        ];
    }
}
