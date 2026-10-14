<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
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
     */
    public function store(Request $request)
    {
        $validated = $request->validate($this->rules(), $this->messages());

        Student::create($validated);

        return redirect()->route('students.index')->with('success', 'Student record created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student)
    {
        return view('students.show', compact('student'));
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
     */
    public function update(Request $request, Student $student)
    {
        $validated = $request->validate($this->rules($student), $this->messages());

        $student->update($validated);

        return redirect()->route('students.index')->with('success', 'Student record updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
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
            'enrolled_units' => ['required', 'integer', 'min:1', 'max:30'],
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
            'enrolled_units.min'  => 'Enrolled units must be at least 1.',
            'enrolled_units.max'  => 'Enrolled units cannot exceed 30.',
            'status.in'           => 'Please choose a valid academic status.',
        ];
    }
}
