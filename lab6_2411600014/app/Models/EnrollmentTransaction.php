<?php

namespace App\Models;

use App\Observers\EnrollmentTransactionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Lab 6 "InventoryTransaction" for the Student Grade Portal.
 * Every change to a student's enrolled units is logged here (audit trail).
 */
#[ObservedBy([EnrollmentTransactionObserver::class])]
class EnrollmentTransaction extends Model
{
    use HasFactory;

    public const TYPE_ADD = 'add';             // stock in
    public const TYPE_DROP = 'drop';           // stock out
    public const TYPE_ADJUSTMENT = 'adjustment';

    public const TYPES = [
        self::TYPE_ADD        => 'Add Units',
        self::TYPE_DROP       => 'Drop Units',
        self::TYPE_ADJUSTMENT => 'Adjustment',
    ];

    /** Types a user can pick on the normal "Record Transaction" form. */
    public const FORM_TYPES = [
        self::TYPE_ADD  => 'Add Units',
        self::TYPE_DROP => 'Drop Units',
    ];

    public const TYPE_BADGES = [
        self::TYPE_ADD        => 'bg-success',
        self::TYPE_DROP       => 'bg-danger',
        self::TYPE_ADJUSTMENT => 'bg-info text-dark',
    ];

    public const ADJUSTMENT_REASONS = [
        'Registrar record audit'       => 'Registrar record audit (count mismatch)',
        'Unlogged add/drop'            => 'Add/drop was never logged',
        'Cross-enrollment correction'  => 'Cross-enrollment correction',
        'Data entry error'             => 'Data entry error',
        'Other'                        => 'Other',
    ];

    protected $fillable = [
        'student_id',
        'user_id',
        'type',
        'quantity',
        'reference_number',
        'reason',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    // ---------------------------------------------------------------- Relations

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ------------------------------------------------------------------- Scopes

    /** Transactions that increased units (stock in). */
    public function scopeUnitsIn(Builder $query): Builder
    {
        return $query->where('quantity', '>', 0);
    }

    /** Transactions that decreased units (stock out). */
    public function scopeUnitsOut(Builder $query): Builder
    {
        return $query->where('quantity', '<', 0);
    }

    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderByDesc('created_at')->orderByDesc('id');
    }

    // ---------------------------------------------------------------- Accessors

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst((string) $this->type);
    }

    public function getTypeBadgeAttribute(): string
    {
        return self::TYPE_BADGES[$this->type] ?? 'bg-secondary';
    }

    /** "+3" / "-5" for display. */
    public function getSignedQuantityAttribute(): string
    {
        return $this->quantity > 0 ? '+' . $this->quantity : (string) $this->quantity;
    }
}
