@extends('layouts.app')

@section('title', 'Students List - Student Grade Portal')

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="fw-bold" style="color: #612D53;">Student Records</h2>
    </div>
    <div class="col-md-6 text-end">
        <a href="{{ route('students.create') }}" class="btn btn-primary">
            + Add New Student
        </a>
    </div>
</div>

{{-- Flash Message Notification --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card shadow-sm">
    <div class="card-header">
        Registered Students List
    </div>
    <div class="card-body">
        @if($students->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Course</th>
                            <th>GPA</th>
                            <th>Units</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                            <tr>
                                <td class="fw-bold">{{ $student->student_id }}</td>
                                <td>{{ $student->name }}</td>
                                <td>{{ $student->course }}</td>
                                <td>{{ number_format($student->gpa, 2) }}</td>
                                <td>{{ $student->enrolled_units }}</td>
                                <td>
                                    <span class="badge bg-{{ $student->status == 'Regular' ? 'success' : 'warning' }}">
                                        {{ $student->status }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('students.show', $student->id) }}" class="btn btn-sm btn-info text-white">View</a>
                                    <a href="{{ route('students.edit', $student->id) }}" class="btn btn-sm btn-warning text-white">Edit</a>
                                    <form action="{{ route('students.destroy', $student->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this student record?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-4">
                <p class="text-muted mb-3">No student records found in the system yet.</p>
                <a href="{{ route('students.create') }}" class="btn btn-primary btn-sm">Add Your First Student</a>
            </div>
        @endif
    </div>
</div>
@endsection