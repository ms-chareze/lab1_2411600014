<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    protected $fillable = [
        'student_id',
        'name',
        'course',
        'gpa',
        'enrolled_units',
        'status',
    ];

    protected $casts = [
        'gpa' => 'float',
        'enrolled_units' => 'integer',
    ];

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
}
