<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $students = Student::all();
        return view('students.index', compact('students'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('students.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id'     => 'required|unique:students,student_id',
            'name'           => 'required|string|max:255',
            'course'         => 'required|string|max:255',
            'gpa'            => 'required|numeric|min:1.00|max:5.00',
            'enrolled_units' => 'required|integer|min:1',
            'status'         => 'required|string',
        ], [
            'student_id.required' => 'The student ID field is required.',
            'student_id.unique'   => 'This student ID is already registered in the system.',
            'gpa.min'             => 'The GPA must be at least 1.00.',
            'gpa.max'             => 'The GPA cannot exceed 5.00.',
            'enrolled_units.min'  => 'Enrolled units must be at least 1.',
        ]);

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
        return view('students.edit', compact('student'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'student_id'     => 'required|unique:students,student_id,' . $student->id,
            'name'           => 'required|string|max:255',
            'course'         => 'required|string|max:255',
            'gpa'            => 'required|numeric|min:1.00|max:5.00',
            'enrolled_units' => 'required|integer|min:1',
            'status'         => 'required|string',
        ], [
            'student_id.required' => 'The student ID field is required.',
            'student_id.unique'   => 'This student ID is already registered in the system.',
            'gpa.min'             => 'The GPA must be at least 1.00.',
            'gpa.max'             => 'The GPA cannot exceed 5.00.',
            'enrolled_units.min'  => 'Enrolled units must be at least 1.',
        ]);

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
}