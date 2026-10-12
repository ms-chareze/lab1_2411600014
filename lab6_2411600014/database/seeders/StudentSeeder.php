<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = [
            [
                'student_id' => 'STU-2026-001',
                'name' => 'Alex Smith',
                'course' => 'BS Office Administration',
                'gpa' => 1.25,
                'enrolled_units' => 21,
                'status' => 'Regular',
            ],
            [
                'student_id' => 'STU-2026-002',
                'name' => 'Maria Santos',
                'course' => 'BS Information Technology',
                'gpa' => 1.75,
                'enrolled_units' => 18,
                'status' => 'Regular',
            ],
            [
                'student_id' => 'STU-2026-003',
                'name' => 'Juan Dela Cruz',
                'course' => 'BS Business Administration',
                'gpa' => 2.25,
                'enrolled_units' => 15,
                'status' => 'Irregular',
            ],
            [
                'student_id' => 'STU-2026-004',
                'name' => 'Sarah Johnson',
                'course' => 'BS Office Administration',
                'gpa' => 1.50,
                'enrolled_units' => 21,
                'status' => 'Regular',
            ],
            [
                'student_id' => 'STU-2026-005',
                'name' => 'Michael Brown',
                'course' => 'BS Information Technology',
                'gpa' => 2.75,
                'enrolled_units' => 12,
                'status' => 'Irregular',
            ],
            [
                'student_id' => 'STU-2026-006',
                'name' => 'Emily Davis',
                'course' => 'BS Accountancy',
                'gpa' => 1.10,
                'enrolled_units' => 24,
                'status' => 'Regular',
            ],
            [
                'student_id' => 'STU-2026-007',
                'name' => 'James Wilson',
                'course' => 'BS Business Administration',
                'gpa' => 2.00,
                'enrolled_units' => 18,
                'status' => 'Regular',
            ],
            [
                'student_id' => 'STU-2026-008',
                'name' => 'Jessica Martinez',
                'course' => 'BS Office Administration',
                'gpa' => 1.80,
                'enrolled_units' => 19,
                'status' => 'Regular',
            ],
            [
                'student_id' => 'STU-2026-009',
                'name' => 'David Taylor',
                'course' => 'BS Information Technology',
                'gpa' => 3.00,
                'enrolled_units' => 9,
                'status' => 'Irregular',
            ],
            [
                'student_id' => 'STU-2026-010',
                'name' => 'Chloe Anderson',
                'course' => 'BS Accountancy',
                'gpa' => 1.20,
                'enrolled_units' => 22,
                'status' => 'Regular',
            ],
            [
                'student_id' => 'STU-2026-011',
                'name' => 'Daniel Thomas',
                'course' => 'BS Business Administration',
                'gpa' => 2.50,
                'enrolled_units' => 15,
                'status' => 'Irregular',
            ],
            [
                'student_id' => 'STU-2026-012',
                'name' => 'Sophia Hernandez',
                'course' => 'BS Office Administration',
                'gpa' => 1.40,
                'enrolled_units' => 20,
                'status' => 'Regular',
            ],
            [
                'student_id' => 'STU-2026-013',
                'name' => 'Matthew Moore',
                'course' => 'BS Information Technology',
                'gpa' => 1.90,
                'enrolled_units' => 18,
                'status' => 'Regular',
            ],
            [
                'student_id' => 'STU-2026-014',
                'name' => 'Olivia Martin',
                'course' => 'BS Accountancy',
                'gpa' => 2.60,
                'enrolled_units' => 14,
                'status' => 'Irregular',
            ],
            [
                'student_id' => 'STU-2026-015',
                'name' => 'Ethan Jackson',
                'course' => 'BS Business Administration',
                'gpa' => 1.65,
                'enrolled_units' => 21,
                'status' => 'Regular',
            ],
        ];

        foreach ($students as $student) {
            Student::create($student);
        }
    }
}