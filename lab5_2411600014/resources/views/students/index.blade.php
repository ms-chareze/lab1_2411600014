@extends('layouts.app')

@section('title', 'Student Records - Student Grade Portal')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h2 class="page-title mb-0">Student Records</h2>
    <a href="{{ route('students.create') }}" class="btn btn-primary btn-sm fw-semibold">+ Add New Student</a>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-0">
        @if($students->count() > 0)
            <div class="table-responsive">
                <table class="table table-theme table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="py-3 px-3">Student ID</th>
                            <th class="py-3">Name</th>
                            <th class="py-3">Course</th>
                            <th class="py-3">GPA</th>
                            <th class="py-3">Units</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                            <tr>
                                <td class="px-3 fw-bold">{{ $student->student_id }}</td>
                                <td>{{ $student->name }}</td>
                                <td>{{ $student->course }}</td>
                                <td>{{ number_format($student->gpa, 2) }}</td>
                                <td>{{ $student->enrolled_units }}</td>
                                <td><span class="badge {{ $student->status_badge }}">{{ $student->status }}</span></td>
                                <td class="text-center text-nowrap">
                                    <a href="{{ route('students.show', $student) }}" class="btn btn-sm btn-outline-primary">View</a>
                                    <a href="{{ route('students.edit', $student) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <form action="{{ route('students.destroy', $student) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this student record?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white py-3">
                <span class="small text-muted">Showing {{ $students->count() }} {{ Str::plural('entry', $students->count()) }}</span>
            </div>
        @else
            <div class="text-center py-5">
                <p class="text-muted mb-3">No student records found in the system yet.</p>
                <a href="{{ route('students.create') }}" class="btn btn-primary btn-sm">Add Your First Student</a>
            </div>
        @endif
    </div>
</div>
@endsection
