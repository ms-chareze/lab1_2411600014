@extends('layouts.app')

@section('title', 'View Student - Student Grade Portal')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="row mb-3">
            <div class="col-md-6">
                <h2 class="fw-bold" style="color: #612D53;">Student Details</h2>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('students.index') }}" class="btn btn-secondary btn-sm">← Back to List</a>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header text-white" style="background-color: #612D53;">
                Record ID: {{ $student->student_id }}
            </div>
            <div class="card-body">
                <table class="table table-bordered align-middle">
                    <tr>
                        <th class="w-25 bg-light">Student ID</th>
                        <td class="fw-bold">{{ $student->student_id }}</td>
                    </tr>
                    <tr>
                        <th class="bg-light">Full Name</th>
                        <td>{{ $student->name }}</td>
                    </tr>
                    <tr>
                        <th class="bg-light">Course / Program</th>
                        <td>{{ $student->course }}</td>
                    </tr>
                    <tr>
                        <th class="bg-light">GPA</th>
                        <td>{{ number_format($student->gpa, 2) }}</td>
                    </tr>
                    <tr>
                        <th class="bg-light">Enrolled Units</th>
                        <td>{{ $student->enrolled_units }}</td>
                    </tr>
                    <tr>
                        <th class="bg-light">Status</th>
                        <td>
                            <span class="badge bg-{{ $student->status == 'Regular' ? 'success' : 'warning' }}">
                                {{ $student->status }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th class="bg-light">Date Created</th>
                        <td>{{ $student->created_at ? $student->created_at->format('F d, Y h:i A') : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th class="bg-light">Last Updated</th>
                        <td>{{ $student->updated_at ? $student->updated_at->format('F d, Y h:i A') : 'N/A' }}</td>
                    </tr>
                </table>

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('students.edit', $student->id) }}" class="btn btn-warning text-white">Edit Record</a>
                    
                    <form action="{{ route('students.destroy', $student->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this student record?');">
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