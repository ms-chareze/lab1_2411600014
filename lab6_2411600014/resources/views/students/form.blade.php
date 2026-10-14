@extends('layouts.app')

@section('title', isset($student) ? 'Edit Student - Student Grade Portal' : 'Add New Student - Student Grade Portal')

@php
    $courses = ['BS Information Technology', 'BS Computer Science', 'BS Business Administration', 'BS Office Administration', 'BS Accountancy'];
@endphp

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-gradient-primary text-white py-3">
                <h5 class="mb-0 fw-bold">
                    {{ isset($student) ? 'Edit Student Record: ' . $student->name : 'Add New Student Record' }}
                </h5>
            </div>
            <div class="card-body p-4">
                {{-- Validation error messages --}}
                @if ($errors->any())
                    <div class="alert alert-danger border-0">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Same form for create and edit --}}
                <form action="{{ isset($student) ? route('students.update', $student) : route('students.store') }}" method="POST">
                    @csrf
                    @if(isset($student))
                        @method('PUT')
                    @endif

                    <div class="mb-3">
                        <label for="student_id" class="form-label">Student ID</label>
                        <input type="text" class="form-control @error('student_id') is-invalid @enderror" id="student_id" name="student_id"
                               value="{{ old('student_id', $student->student_id ?? '') }}" placeholder="e.g. STU-2026-016" required>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                               value="{{ old('name', $student->name ?? '') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="course" class="form-label">Course / Program</label>
                        <input type="text" class="form-control @error('course') is-invalid @enderror" id="course" name="course" list="courseOptions"
                               value="{{ old('course', $student->course ?? '') }}" required>
                        <datalist id="courseOptions">
                            @foreach($courses as $course)
                                <option value="{{ $course }}"></option>
                            @endforeach
                        </datalist>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="gpa" class="form-label">GPA (1.00 - 5.00)</label>
                            <input type="number" step="0.01" min="1.00" max="5.00" class="form-control @error('gpa') is-invalid @enderror" id="gpa" name="gpa"
                                   value="{{ old('gpa', $student->gpa ?? '') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="enrolled_units" class="form-label">Enrolled Units</label>
                            <input type="number" min="0" max="{{ App\Models\Student::MAX_UNITS }}" class="form-control @error('enrolled_units') is-invalid @enderror" id="enrolled_units" name="enrolled_units"
                                   value="{{ old('enrolled_units', $student->enrolled_units ?? '') }}" required>
                            <div class="form-text">
                                {{ isset($student) ? 'Changing this logs an adjustment transaction.' : 'Logged as an opening-balance transaction.' }}
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="min_units" class="form-label">Minimum Units (low-load alert level)</label>
                        <input type="number" min="0" max="{{ App\Models\Student::MAX_UNITS }}" class="form-control @error('min_units') is-invalid @enderror" id="min_units" name="min_units"
                               value="{{ old('min_units', $student->min_units ?? App\Models\Student::DEFAULT_MIN_UNITS) }}" required>
                        <div class="form-text">A notification is sent when enrolled units fall to or below this number.</div>
                    </div>

                    <div class="mb-4">
                        <label for="status" class="form-label">Academic Status</label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="">Select Status</option>
                            @foreach(array_keys(App\Models\Student::STATUSES) as $status)
                                <option value="{{ $status }}" @selected(old('status', $student->status ?? '') === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('students.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary fw-semibold">
                            {{ isset($student) ? 'Update Student Record' : 'Save Student Record' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
