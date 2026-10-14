<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Product */
class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'sku'                => $this->sku,
            'name'               => $this->name,
            'category'           => $this->category,
            'price'              => (float) $this->price,
            'quantity'           => $this->quantity,
            'reorder_level'      => $this->reorder_level,
            'stock_status'       => $this->stock_status,
            'stock_status_color' => $this->stock_status_color,
            'is_low_stock'       => $this->isLowStock(),
            'is_out_of_stock'    => $this->isOutOfStock(),
            'stock_value'        => $this->stock_value,
            'url'                => route('products.show', $this->resource),
        ];
    }
}
