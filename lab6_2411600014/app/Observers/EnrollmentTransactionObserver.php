<?php

namespace App\Observers;

use App\Exceptions\InvalidUnitMovementException;
use App\Models\EnrollmentTransaction;
use App\Models\Student;
use App\Models\User;
use App\Notifications\LowUnitLoadNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

/**
 * Keeps students.enrolled_units in sync with the transaction log.
 *
 *  creating -> VALIDATE (runs BEFORE the row is saved, so a bad movement
 *              never reaches the table)
 *  created  -> APPLY the change to the student and check for low load
 *  deleting / deleted -> same idea in reverse
 *
 * Callers should wrap create()/delete() in DB::transaction() so that the
 * log row and the student update succeed or fail together.
 */
class EnrollmentTransactionObserver
{
    public function creating(EnrollmentTransaction $transaction): void
    {
        $delta = (int) $transaction->quantity;

        if ($delta === 0) {
            throw new InvalidUnitMovementException('A transaction must move at least 1 unit.');
        }

        $student = $this->lockStudent((int) $transaction->student_id);

        $this->assertWithinLimits($student, $delta);
    }

    public function created(EnrollmentTransaction $transaction): void
    {
        $this->applyDelta((int) $transaction->student_id, (int) $transaction->quantity);
    }

    /** Deleting a transaction reverses its effect, if that is still possible. */
    public function deleting(EnrollmentTransaction $transaction): void
    {
        $student = $this->lockStudent((int) $transaction->student_id);

        $this->assertWithinLimits($student, -((int) $transaction->quantity), true);
    }

    public function deleted(EnrollmentTransaction $transaction): void
    {
        $this->applyDelta((int) $transaction->student_id, -((int) $transaction->quantity));
    }

    // ------------------------------------------------------------------ helpers

    private function lockStudent(int $studentId): Student
    {
        $student = Student::query()->lockForUpdate()->find($studentId);

        if (! $student) {
            throw new InvalidUnitMovementException('The selected student no longer exists.');
        }

        return $student;
    }

    private function assertWithinLimits(Student $student, int $delta, bool $isReversal = false): void
    {
        $result = $student->enrolled_units + $delta;

        if ($result < 0) {
            $message = $isReversal
                ? "Cannot delete this transaction: reversing it would leave {$student->name} with {$result} units."
                : sprintf(
                    'Cannot drop %d unit(s): %s only has %d enrolled unit(s).',
                    abs($delta),
                    $student->name,
                    $student->enrolled_units
                );

            throw new InvalidUnitMovementException($message);
        }

        if ($result > Student::MAX_UNITS) {
            $message = $isReversal
                ? "Cannot delete this transaction: reversing it would put {$student->name} at {$result} units (max " . Student::MAX_UNITS . ').'
                : sprintf(
                    'Cannot add %d unit(s): %s would exceed the %d-unit maximum load (currently %d).',
                    $delta,
                    $student->name,
                    Student::MAX_UNITS,
                    $student->enrolled_units
                );

            throw new InvalidUnitMovementException($message);
        }
    }

    /**
     * Apply the signed change to the student, then run the low-load check.
     * Joins the caller's DB transaction when there is one.
     */
    private function applyDelta(int $studentId, int $delta): void
    {
        DB::transaction(function () use ($studentId, $delta) {
            $student = Student::query()->lockForUpdate()->findOrFail($studentId);

            $student->enrolled_units = $student->enrolled_units + $delta;

            // Duplicate-notification guard:
            //  - first time the student drops to/below min units -> notify, remember when
            //  - while still low -> stay quiet
            //  - once back above the minimum -> reset so a future drop notifies again
            $shouldNotify = false;

            if ($student->isLowLoad()) {
                if ($student->low_load_notified_at === null) {
                    $student->low_load_notified_at = now();
                    $shouldNotify = true;
                }
            } else {
                $student->low_load_notified_at = null;
            }

            $student->save();

            if ($shouldNotify) {
                $this->notifyStaff($student);
            }
        });
    }

    private function notifyStaff(Student $student): void
    {
        $recipients = User::query()->where('is_admin', true)->get();

        // No admin yet? Fall back to everybody so the alert is never lost.
        if ($recipients->isEmpty()) {
            $recipients = User::all();
        }

        Notification::send($recipients, new LowUnitLoadNotification($student));
    }
}
