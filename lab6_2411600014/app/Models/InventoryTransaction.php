<?php

namespace App\Models;

use App\Observers\InventoryTransactionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One row in the stock ledger.
 *
 * The #[ObservedBy] attribute (Laravel 10.x+) registers the observer, so no
 * code is needed in AppServiceProvider. The observer is what actually moves
 * the product quantity - see InventoryTransactionObserver.
 */
#[ObservedBy([InventoryTransactionObserver::class])]
class InventoryTransaction extends Model
{
    use HasFactory;

    public const TYPE_IN         = 'stock_in';
    public const TYPE_OUT        = 'stock_out';
    public const TYPE_ADJUSTMENT = 'adjustment';

    protected $fillable = [
        'product_id',
        'user_id',
        'type',
        'quantity',
        'quantity_before',
        'quantity_after',
        'reference_number',
        'reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity'        => 'integer',
            'quantity_before' => 'integer',
            'quantity_after'  => 'integer',
        ];
    }

    /** Machine value => human label. */
    public static function types(): array
    {
        return [
            self::TYPE_IN         => 'Stock In',
            self::TYPE_OUT        => 'Stock Out',
            self::TYPE_ADJUSTMENT => 'Adjustment',
        ];
    }

    /** Types a user may pick on the normal "record transaction" form. */
    public static function manualTypes(): array
    {
        return [
            self::TYPE_IN  => 'Stock In',
            self::TYPE_OUT => 'Stock Out',
        ];
    }

    /* -----------------------------------------------------------------
     |  Relationships
     | -----------------------------------------------------------------*/

    /** withTrashed(): history must still show products that were soft deleted. */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    /** Who performed the movement (audit trail). */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withDefault(['name' => 'Deleted user']);
    }

    /* -----------------------------------------------------------------
     |  Query scopes
     | -----------------------------------------------------------------*/

    public function scopeStockIn(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_IN);
    }

    public function scopeStockOut(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_OUT);
    }

    /** Newest first. The id tiebreaker keeps order stable within one second. */
    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderByDesc('created_at')->orderByDesc('id');
    }

    /** Optional filters shared by the index page, product history and reports. */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['product_id'] ?? null, fn ($q, $v) => $q->where('product_id', $v))
            ->when($filters['type'] ?? null, fn ($q, $v) => $q->where('type', $v))
            ->when($filters['from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['to'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v));
    }

    /* -----------------------------------------------------------------
     |  Presentation helpers
     | -----------------------------------------------------------------*/

    public function getTypeLabelAttribute(): string
    {
        return self::types()[$this->type] ?? ucfirst(str_replace('_', ' ', $this->type));
    }

    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_IN  => 'success',
            self::TYPE_OUT => 'danger',
            default        => 'info',
        };
    }

    /** "+25" / "-10" - the +/- indicator the views display. */
    public function getSignedQuantityAttribute(): string
    {
        return ($this->quantity > 0 ? '+' : '') . $this->quantity;
    }
}
