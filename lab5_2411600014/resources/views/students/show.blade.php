@extends('layouts.app')

@section('title', 'View Student - Student Grade Portal')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="page-title mb-0">Student Details</h2>
            <a href="{{ route('students.index') }}" class="btn btn-outline-secondary btn-sm">&larr; Back to List</a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-gradient-primary text-white py-3">
                <h5 class="mb-0 fw-bold">Record ID: {{ $student->student_id }}</h5>
            </div>
            <div class="card-body p-4">
                <table class="table table-bordered align-middle mb-0">
                    <tr>
                        <th class="detail-label">Student ID</th>
                        <td class="fw-bold">{{ $student->student_id }}</td>
                    </tr>
                    <tr>
                        <th class="detail-label">Full Name</th>
                        <td>{{ $student->name }}</td>
                    </tr>
                    <tr>
                        <th class="detail-label">Course / Program</th>
                        <td>{{ $student->course }}</td>
                    </tr>
                    <tr>
                        <th class="detail-label">GPA</th>
                        <td>{{ number_format($student->gpa, 2) }}</td>
                    </tr>
                    <tr>
                        <th class="detail-label">Enrolled Units</th>
                        <td>{{ $student->enrolled_units }}</td>
                    </tr>
                    <tr>
                        <th class="detail-label">Academic Status</th>
                        <td><span class="badge {{ $student->status_badge }}">{{ $student->status }}</span></td>
                    </tr>
                    <tr>
                        <th class="detail-label">Date Created</th>
                        <td>{{ $student->created_at ? $student->created_at->format('F d, Y h:i A') : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th class="detail-label">Last Updated</th>
                        <td>{{ $student->updated_at ? $student->updated_at->format('F d, Y h:i A') : 'N/A' }}</td>
                    </tr>
                </table>

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('students.edit', $student) }}" class="btn btn-primary fw-semibold">Edit Record</a>

                    <form action="{{ route('students.destroy', $student) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('Are you sure you want to delete this student record?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete Record</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
