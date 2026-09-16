@extends('layouts.app')

@section('title', isset($student) ? 'Edit Student - Student Grade Portal' : 'Add New Student - Student Grade Portal')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header">
                {{ isset($student) ? 'Edit Student Record: ' . $student->name : 'Add New Student Record' }}
            </div>
            <div class="card-body">
                {{-- Display Validation Error Messages --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Dynamic Form Action & Method --}}
                <form action="{{ isset($student) ? route('students.update', $student->id) : route('students.store') }}" method="POST">
                    @csrf
                    @if(isset($student))
                        @method('PUT')
                    @endif

                    <div class="mb-3">
                        <label for="student_id" class="form-label fw-bold">Student ID</label>
                        <input type="text" class="form-control" id="student_id" name="student_id" value="{{ old('student_id', $student->student_id ?? '') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label fw-bold">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $student->name ?? '') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="course" class="form-label fw-bold">Course / Program</label>
                        <input type="text" class="form-control" id="course" name="course" value="{{ old('course', $student->course ?? '') }}" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="gpa" class="form-label fw-bold">GPA (1.00 - 5.00)</label>
                            <input type="number" step="0.01" min="1.00" max="5.00" class="form-control" id="gpa" name="gpa" value="{{ old('gpa', $student->gpa ?? '') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="enrolled_units" class="form-label fw-bold">Enrolled Units</label>
                            <input type="number" min="1" class="form-control" id="enrolled_units" name="enrolled_units" value="{{ old('enrolled_units', $student->enrolled_units ?? '') }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label fw-bold">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="">Select Status</option>
                            <option value="Regular" {{ (old('status', $student->status ?? '') == 'Regular') ? 'selected' : '' }}>Regular</option>
                            <option value="Irregular" {{ (old('status', $student->status ?? '') == 'Irregular') ? 'selected' : '' }}>Irregular</option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('students.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            {{ isset($student) ? 'Update Student Record' : 'Save Student Record' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection