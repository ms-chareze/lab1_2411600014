<?php

namespace App\Http\Controllers;

use App\Models\EnrollmentTransaction;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $students = Student::orderBy('student_id')->get();

        return view('students.index', compact('students'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('students.form');
    }

    /**
     * Store a newly created resource in storage.
     *
     * Lab 6: the starting units are NOT written directly. The student is created
     * with 0 units and an "opening balance" transaction is logged, so the audit
     * trail explains every unit on the record.
     */
    public function store(Request $request)
    {
        $validated = $request->validate($this->rules(), $this->messages());

        $units = (int) $validated['enrolled_units'];
        $validated['enrolled_units'] = 0;

        DB::transaction(function () use ($validated, $units, $request) {
            $student = Student::create($validated);

            if ($units > 0) {
                EnrollmentTransaction::create([
                    'student_id'       => $student->id,
                    'user_id'          => $request->user()->id,
                    'type'             => EnrollmentTransaction::TYPE_ADD,
                    'quantity'         => $units,
                    'reference_number' => 'OPENING-BALANCE',
                    'notes'            => 'Initial enrollment when the student record was created.',
                ]);
            }
        });

        return redirect()->route('students.index')->with('success', 'Student record created successfully.');
    }

    /**
     * Display the specified resource (with transaction history, Lab 6 Part 9).
     */
    public function show(Request $request, Student $student)
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to'   => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $from = $filters['from'] ?? null;
        $to = $filters['to'] ?? null;

        $history = $student->transactionHistory($from, $to);

        return view('students.show', compact('student', 'history', 'from', 'to'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Student $student)
    {
        return view('students.form', compact('student'));
    }

    /**
     * Update the specified resource in storage.
     *
     * Lab 6: if the units were changed on the form, the change is logged as an
     * adjustment transaction instead of silently overwriting the value.
     */
    public function update(Request $request, Student $student)
    {
        $validated = $request->validate($this->rules($student), $this->messages());

        $newUnits = (int) $validated['enrolled_units'];
        unset($validated['enrolled_units']);

        DB::transaction(function () use ($student, $validated, $newUnits, $request) {
            $current = Student::query()->lockForUpdate()->findOrFail($student->id);
            $current->update($validated);

            $difference = $newUnits - $current->enrolled_units;

            if ($difference !== 0) {
                EnrollmentTransaction::create([
                    'student_id'       => $current->id,
                    'user_id'          => $request->user()->id,
                    'type'             => EnrollmentTransaction::TYPE_ADJUSTMENT,
                    'quantity'         => $difference,
                    'reference_number' => 'PROFILE-EDIT',
                    'reason'           => 'Data entry error',
                    'notes'            => 'Units changed from the student edit form.',
                ]);
            }
        });

        return redirect()->route('students.index')->with('success', 'Student record updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     * (The student's transactions are removed too: cascade delete.)
     */
    public function destroy(Student $student)
    {
        $student->delete();

        return redirect()->route('students.index')->with('success', 'Student record deleted successfully.');
    }

    /**
     * Validation rules shared by store() and update().
     */
    private function rules(?Student $student = null): array
    {
        return [
            'student_id'     => ['required', 'string', 'max:50', Rule::unique('students', 'student_id')->ignore($student?->id)],
            'name'           => ['required', 'string', 'max:255'],
            'course'         => ['required', 'string', 'max:255'],
            'gpa'            => ['required', 'numeric', 'min:1.00', 'max:5.00'],
            'enrolled_units' => ['required', 'integer', 'min:0', 'max:' . Student::MAX_UNITS],
            'min_units'      => ['required', 'integer', 'min:0', 'max:' . Student::MAX_UNITS],
            'status'         => ['required', Rule::in(array_keys(Student::STATUSES))],
        ];
    }

    /**
     * Custom validation error messages.
     */
    private function messages(): array
    {
        return [
            'student_id.required' => 'The student ID field is required.',
            'student_id.unique'   => 'This student ID is already registered in the system.',
            'gpa.min'             => 'The GPA must be at least 1.00.',
            'gpa.max'             => 'The GPA cannot exceed 5.00.',
            'enrolled_units.min'  => 'Enrolled units cannot be negative.',
            'enrolled_units.max'  => 'Enrolled units cannot exceed ' . Student::MAX_UNITS . '.',
            'min_units.max'       => 'Minimum units cannot exceed ' . Student::MAX_UNITS . '.',
            'status.in'           => 'Please choose a valid academic status.',
        ];
    }
}
