<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sku',
        'name',
        'category',
        'description',
        'price',
        'quantity',
        'reorder_level',
        'low_stock_notified_at',
    ];

    protected function casts(): array
    {
        return [
            'price'                 => 'decimal:2',
            'quantity'              => 'integer',
            'reorder_level'         => 'integer',
            'low_stock_notified_at' => 'datetime',
        ];
    }

    /* -----------------------------------------------------------------
     |  Part 4 Step 1 - Relationships
     | -----------------------------------------------------------------*/

    /** Every stock movement recorded for this product. */
    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    /** The single most recent stock movement (ties broken by id). */
    public function latestTransaction(): HasOne
    {
        return $this->hasOne(InventoryTransaction::class)->latestOfMany();
    }

    /* -----------------------------------------------------------------
     |  Part 4 Step 2 - Helper methods (business logic lives with the data)
     | -----------------------------------------------------------------*/

    /** True when quantity is at or below the reorder level. */
    public function isLowStock(): bool
    {
        return $this->quantity <= $this->reorder_level;
    }

    /** True when there is nothing left to sell. */
    public function isOutOfStock(): bool
    {
        return $this->quantity <= 0;
    }

    /** "In Stock", "Low Stock" or "Out of Stock" - used by views and the API. */
    public function getStockStatusAttribute(): string
    {
        if ($this->isOutOfStock()) {
            return 'Out of Stock';
        }

        return $this->isLowStock() ? 'Low Stock' : 'In Stock';
    }

    /** Bootstrap contextual colour for the status badge. */
    public function getStockStatusColorAttribute(): string
    {
        if ($this->isOutOfStock()) {
            return 'danger';
        }

        return $this->isLowStock() ? 'warning' : 'success';
    }

    /** Current value of the stock on hand (quantity x unit price). */
    public function getStockValueAttribute(): float
    {
        return round($this->quantity * (float) $this->price, 2);
    }

    /* -----------------------------------------------------------------
     |  Part 4 Step 3 - Query scopes
     | -----------------------------------------------------------------*/

    /** Products at or below their own reorder level (includes out of stock). */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('quantity', '<=', 'reorder_level');
    }

    /** Products with zero (or negative, should it ever happen) quantity. */
    public function scopeOutOfStock(Builder $query): Builder
    {
        return $query->where('quantity', '<=', 0);
    }

    public function scopeInCategory(Builder $query, ?string $category): Builder
    {
        return $category ? $query->where('category', $category) : $query;
    }
}
