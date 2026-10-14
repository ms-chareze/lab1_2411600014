<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\InventoryTransaction */
class InventoryTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $product = $this->product;

        return [
            'id'               => $this->id,
            'type'             => $this->type,
            'type_label'       => $this->type_label,
            'type_color'       => $this->type_color,
            'quantity'         => $this->quantity,
            'signed_quantity'  => $this->signed_quantity,
            'quantity_before'  => $this->quantity_before,
            'quantity_after'   => $this->quantity_after,
            'reference_number' => $this->reference_number,
            'reason'           => $this->reason,
            'product'          => [
                'id'   => $product->id,
                'name' => $product->name,
                'sku'  => $product->sku,
                // Soft-deleted products have no show page.
                'url'  => $product->trashed() ? null : route('products.show', $product),
            ],
            'user'             => $this->user->name,
            'url'              => route('transactions.show', $this->resource),
            'created_at'       => $this->created_at?->toIso8601String(),
            'created_at_human' => $this->created_at?->diffForHumans(),
        ];
    }
}
