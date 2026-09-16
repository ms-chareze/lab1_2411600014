<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight" style="color: #612D53;">
                {{ __('Dashboard Overview') }}
            </h2>
            <a href="{{ route('students.create') }}" class="btn text-white shadow-sm" style="background-color: #612D53;">
                <i class="bi bi-plus-lg"></i> Add New Student
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Statistics Cards Row -->
            <div class="row g-4 mb-4">
                <!-- Total Students Card -->
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #612D53 !important;">
                        <div class="card-body">
                            <div class="text-muted small text-uppercase fw-bold">Total Students</div>
                            <div class="fs-3 fw-bold mt-2" style="color: #612D53;">{{ $totalStudents ?? 0 }}</div>
                        </div>
                    </div>
                </div>

                <!-- Average GPA Card -->
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #A084DC !important;">
                        <div class="card-body">
                            <div class="text-muted small text-uppercase fw-bold">Average GPA</div>
                            <div class="fs-3 fw-bold mt-2 text-dark">
                                {{ isset($averageGpa) ? number_format($averageGpa, 2) : '0.00' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Regular Students Card -->
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #198754 !important;">
                        <div class="card-body">
                            <div class="text-muted small text-uppercase fw-bold">Regular Students</div>
                            <div class="fs-3 fw-bold mt-2 text-success">{{ $regularCount ?? 0 }}</div>
                        </div>
                    </div>
                </div>

                <!-- Irregular Students Card -->
                <div class="col-md-3 col-sm-6">
                    <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #ffc107 !important;">
                        <div class="card-body">
                            <div class="text-muted small text-uppercase fw-bold">Irregular Students</div>
                            <div class="fs-3 fw-bold mt-2 text-warning">{{ $irregularCount ?? 0 }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Secondary Info Row -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100" style="background-color: #FFF4F4;">
                        <div class="card-body">
                            <h5 class="fw-bold" style="color: #612D53;">Total Enrolled Units</h5>
                            <p class="fs-4 fw-semibold mb-0 text-dark">{{ $totalUnits ?? 0 }} Units</p>
                            <small class="text-muted">Accumulated units across all active students.</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body d-flex flex-column justify-content-center">
                            <h5 class="fw-bold" style="color: #612D53;">Quick Navigation</h5>
                            <p class="text-muted mb-3">Manage your entire student roster and process grades efficiently.</p>
                            <div>
                                <a href="{{ route('students.index') }}" class="btn btn-outline-dark btn-sm me-2">View All Students</a>
                                <a href="{{ route('students.create') }}" class="btn btn-sm text-white" style="background-color: #612D53;">Register Student</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Students Table Section -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold" style="color: #612D53;">Recently Added Students</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Student ID</th>
                                    <th>Name</th>
                                    <th>Course</th>
                                    <th>GPA</th>
                                    <th>Status</th>
                                    <th class="text-end pe-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $recentStudents = App\Models\Student::latest()->take(5)->get();
                                @endphp

                                @forelse($recentStudents as $student)
                                    <tr>
                                        <td class="ps-3 fw-bold">{{ $student->student_id }}</td>
                                        <td>{{ $student->name }}</td>
                                        <td>{{ $student->course }}</td>
                                        <td>{{ number_format($student->gpa, 2) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $student->status == 'Regular' ? 'success' : 'warning' }}">
                                                {{ $student->status }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-3">
                                            <a href="{{ route('students.show', $student->id) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No student records found yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>