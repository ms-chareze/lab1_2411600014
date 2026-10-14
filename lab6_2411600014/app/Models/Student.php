<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;

class Student extends Model
{
    use HasFactory;

    /**
     * Allowed academic statuses mapped to their Bootstrap badge classes
     * (same Regular / Warning / Probation scheme as Laboratory Exercise 4).
     */
    public const STATUSES = [
        'Regular'   => 'bg-success',
        'Irregular' => 'bg-info text-dark',
        'Warning'   => 'bg-warning text-dark',
        'Probation' => 'bg-danger',
    ];

    /** Statuses that count as an "academic warning". */
    public const WARNING_STATUSES = ['Warning', 'Probation'];

    /** Maximum units a student may carry (upper limit of "stock"). */
    public const MAX_UNITS = 30;

    /** Default minimum full-time load ("reorder level"). */
    public const DEFAULT_MIN_UNITS = 12;

    protected $fillable = [
        'student_id',
        'name',
        'course',
        'gpa',
        'enrolled_units',
        'min_units',
        'status',
    ];

    protected $casts = [
        'gpa' => 'float',
        'enrolled_units' => 'integer',
        'min_units' => 'integer',
        'low_load_notified_at' => 'datetime',
    ];

    // ---------------------------------------------------------------- Relations

    public function transactions(): HasMany
    {
        return $this->hasMany(EnrollmentTransaction::class);
    }

    public function latestTransaction(): HasOne
    {
        return $this->hasOne(EnrollmentTransaction::class)->latestOfMany();
    }

    // --------------------------------------------------------- Lab 5 accessors

    /** Bootstrap badge classes for this student's status. */
    public function getStatusBadgeAttribute(): string
    {
        return self::STATUSES[$this->status] ?? 'bg-secondary';
    }

    /** True when the student is on Warning or Probation. */
    public function needsReview(): bool
    {
        return in_array($this->status, self::WARNING_STATUSES, true);
    }

    // ---------------------------------------- Lab 6 helpers (stock -> unit load)

    /** Low stock => units are at or below the minimum load. */
    public function isLowLoad(): bool
    {
        return $this->enrolled_units <= $this->min_units;
    }

    /** Out of stock => no enrolled units at all. */
    public function isNoLoad(): bool
    {
        return $this->enrolled_units <= 0;
    }

    /** 'Normal Load', 'Low Load' or 'No Load'. */
    public function getLoadStatusAttribute(): string
    {
        if ($this->isNoLoad()) {
            return 'No Load';
        }

        return $this->isLowLoad() ? 'Low Load' : 'Normal Load';
    }

    /** Bootstrap color name: success / warning / danger. */
    public function getLoadStatusColorAttribute(): string
    {
        if ($this->isNoLoad()) {
            return 'danger';
        }

        return $this->isLowLoad() ? 'warning' : 'success';
    }

    // ------------------------------------------------------------------- Scopes

    public function scopeLowLoad(Builder $query): Builder
    {
        return $query->whereColumn('enrolled_units', '<=', 'min_units');
    }

    public function scopeNoLoad(Builder $query): Builder
    {
        return $query->where('enrolled_units', '<=', 0);
    }

    public function scopeInCourse(Builder $query, string $course): Builder
    {
        return $query->where('course', $course);
    }

    // ------------------------------------------------------------------ History

    /**
     * All transactions (newest first) with a running balance column.
     *
     * The balance is rebuilt by walking backwards from the current units, so it
     * always stays consistent even if an older transaction is later deleted.
     * The optional date range only filters what is DISPLAYED.
     */
    public function transactionHistory(?string $from = null, ?string $to = null): Collection
    {
        $balance = $this->enrolled_units;

        $rows = $this->transactions()
            ->with('user')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get()
            ->map(function (EnrollmentTransaction $transaction) use (&$balance) {
                $transaction->balance_after = $balance;
                $balance -= $transaction->quantity;

                return $transaction;
            });

        return $rows
            ->when($from, fn ($c) => $c->filter(fn ($t) => $t->created_at->toDateString() >= $from))
            ->when($to, fn ($c) => $c->filter(fn ($t) => $t->created_at->toDateString() <= $to))
            ->values();
    }
}
