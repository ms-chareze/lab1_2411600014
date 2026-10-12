@extends('layouts.app')

@section('title', 'Add New Student - Student Grade Portal')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header">
                Add New Student Record
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('students.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="student_id" class="form-label fw-bold">Student ID</label>
                        <input type="text" class="form-control" id="student_id" name="student_id" value="{{ old('student_id') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label fw-bold">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="course" class="form-label fw-bold">Course / Program</label>
                        <input type="text" class="form-control" id="course" name="course" value="{{ old('course') }}" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="gpa" class="form-label fw-bold">GPA (1.00 - 5.00)</label>
                            <input type="number" step="0.01" min="1.00" max="5.00" class="form-control" id="gpa" name="gpa" value="{{ old('gpa') }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="enrolled_units" class="form-label fw-bold">Enrolled Units</label>
                            <input type="number" min="1" class="form-control" id="enrolled_units" name="enrolled_units" value="{{ old('enrolled_units') }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label fw-bold">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="">Select Status</option>
                            <option value="Regular" {{ old('status') == 'Regular' ? 'selected' : '' }}>Regular</option>
                            <option value="Irregular" {{ old('status') == 'Irregular' ? 'selected' : '' }}>Irregular</option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('students.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Student Record</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection