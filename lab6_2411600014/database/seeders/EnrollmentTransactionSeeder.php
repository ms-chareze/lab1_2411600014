<?php

namespace Database\Seeders;

use App\Models\EnrollmentTransaction;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Optional demo data:  php artisan db:seed --class=EnrollmentTransactionSeeder
 * Goes through the model, so the observer updates units / sends alerts.
 */
class EnrollmentTransactionSeeder extends Seeder
{
    public function run(): void
    {
        if (EnrollmentTransaction::exists()) {
            $this->command?->info('Transactions already exist - skipping.');
            return;
        }

        $user = User::orderBy('id')->first();
        if (! $user) {
            $this->command?->warn('Create a user first (register), then run this seeder.');
            return;
        }

        // [student_id, type, signed quantity, reference, notes, days ago]
        $moves = [
            ['STU-2026-005', 'add',  3, 'ADD-0001',  'Late enrollment in one subject',        6],
            ['STU-2026-009', 'add',  3, 'ADD-0002',  'Added a retake subject',                5],
            ['STU-2026-001', 'drop', -3, 'DROP-0001', 'Dropped an elective',                  4],
            ['STU-2026-003', 'add',  3, 'ADD-0003',  'Regularization of load',                3],
            ['STU-2026-012', 'drop', -8, 'DROP-0002', 'Dropped two subjects (schedule conflict)', 2],
            ['STU-2026-006', 'add',  3, 'ADD-0004',  'Overload approved by the dean',         1],
        ];

        foreach ($moves as [$studentNumber, $type, $quantity, $reference, $notes, $daysAgo]) {
            $student = Student::where('student_id', $studentNumber)->first();
            if (! $student) {
                continue;
            }

            DB::transaction(function () use ($student, $user, $type, $quantity, $reference, $notes, $daysAgo) {
                $transaction = new EnrollmentTransaction([
                    'student_id'       => $student->id,
                    'user_id'          => $user->id,
                    'type'             => $type,
                    'quantity'         => $quantity,
                    'reference_number' => $reference,
                    'notes'            => $notes,
                ]);
                $transaction->created_at = now()->subDays($daysAgo);
                $transaction->save();
            });
        }
    }
}
