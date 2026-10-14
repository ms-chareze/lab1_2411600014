<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="fw-bold mb-0" style="color: var(--theme-text);" id="dashboardGreeting">Welcome, {{ Auth::user()->name }}!</h2>
            <div class="d-flex gap-2">
                <a href="{{ route('students.create') }}" class="btn btn-primary btn-sm fw-semibold">+ Add New Student</a>
                <a href="{{ route('students.index') }}" class="btn btn-outline-primary btn-sm fw-semibold">View All Students</a>
            </div>
        </div>
    </x-slot>

    <!-- 4 Metrics Display Cards (real database values) -->
    <div class="row text-center mb-4 g-3">
        <div class="col-md-3 col-sm-6">
            <div class="card stat-card shadow-sm border-0 bg-white border-primary-custom h-100">
                <div class="card-body">
                    <div class="card-title text-muted fw-bold">Total Students</div>
                    <div class="card-text fw-bold text-primary-custom">{{ $totalStudents }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card stat-card shadow-sm border-0 bg-white border-success-custom h-100">
                <div class="card-body">
                    <div class="card-title text-muted fw-bold">Average GPA</div>
                    <div class="card-text fw-bold text-success">{{ number_format($averageGpa, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card stat-card shadow-sm border-0 bg-white border-accent h-100">
                <div class="card-body">
                    <div class="card-title text-muted fw-bold">Academic Warnings</div>
                    <div class="card-text fw-bold text-danger">{{ $warningCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card stat-card shadow-sm border-0 bg-white border-primary-custom h-100">
                <div class="card-body">
                    <div class="card-title text-muted fw-bold">Total Units</div>
                    <div class="card-text fw-bold" style="color: var(--theme-text);">{{ number_format($totalUnits) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Academic Alert Box -->
    @if($warningStudents->isNotEmpty())
        <div class="alert alert-danger shadow-sm mb-4 border-0" role="alert">
            <h6 class="alert-heading fw-bold">
                Academic Warning Alert ({{ $warningStudents->count() }} {{ Str::plural('Student', $warningStudents->count()) }} Needing Review)
            </h6>
            <ul class="mb-0 small">
                @foreach($warningStudents as $s)
                    <li>
                        <strong>{{ $s->name }}</strong> ({{ $s->course }}) - GPA: {{ number_format($s->gpa, 2) }} [{{ $s->status }}]
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Visual Charts Section with Explicit Axis Labels (Lab 4 charts) -->
    <div class="row mb-4 g-3">
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 p-3 h-100">
                <h6 class="fw-bold mb-3" style="color: var(--theme-text);">Course Distribution</h6>
                <div class="chart-container" style="height: 220px;">
                    <canvas id="courseChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 p-3 h-100">
                <h6 class="fw-bold mb-3" style="color: var(--theme-text);">Academic Status Ratio</h6>
                <div class="chart-container" style="height: 220px;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 p-3 h-100">
                <h6 class="fw-bold mb-3" style="color: var(--theme-text);">Top Performing GPA</h6>
                <div class="chart-container" style="height: 220px;">
                    <canvas id="topStudentsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recently Added Students -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-bold" style="color: var(--theme-text);">Recently Added Students</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-theme table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="py-3 px-3">Student ID</th>
                            <th class="py-3">Name</th>
                            <th class="py-3">Course</th>
                            <th class="py-3">GPA</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentStudents as $student)
                            <tr>
                                <td class="px-3 fw-bold">{{ $student->student_id }}</td>
                                <td>{{ $student->name }}</td>
                                <td>{{ $student->course }}</td>
                                <td>{{ number_format($student->gpa, 2) }}</td>
                                <td><span class="badge {{ $student->status_badge }}">{{ $student->status }}</span></td>
                                <td class="text-center">
                                    <a href="{{ route('students.show', $student) }}" class="btn btn-sm btn-outline-primary">View</a>
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

    @push('scripts')
        <!-- Chart.js Library -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Data comes straight from DashboardController (database values)
                const chartData = @json($chartData);
                const userName = @json(Auth::user()->name);

                // Time-based greeting (same as Lab 4)
                const greeting = document.getElementById('dashboardGreeting');
                if (greeting) {
                    const hour = new Date().getHours();
                    let timeOfDay = 'Good Morning';
                    if (hour >= 12 && hour < 17) timeOfDay = 'Good Afternoon';
                    else if (hour >= 17) timeOfDay = 'Good Evening';
                    greeting.textContent = `${timeOfDay}, ${userName}!`;
                }

                // Chart 1: Course Distribution (bar)
                new Chart(document.getElementById('courseChart').getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: chartData.course.labels,
                        datasets: [{ label: 'Enrolled Students', data: chartData.course.data, backgroundColor: '#A084DC' }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: { title: { display: true, text: 'Courses' } },
                            y: { title: { display: true, text: 'Student Count' }, beginAtZero: true, ticks: { precision: 0 } }
                        }
                    }
                });

                // Chart 2: Academic Status Ratio (doughnut)
                new Chart(document.getElementById('statusChart').getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: chartData.status.labels,
                        datasets: [{ data: chartData.status.data, backgroundColor: chartData.status.colors }]
                    },
                    options: { responsive: true, maintainAspectRatio: false }
                });

                // Chart 3: Top Performing GPA (horizontal bar)
                new Chart(document.getElementById('topStudentsChart').getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: chartData.top.labels,
                        datasets: [{ label: 'GPA', data: chartData.top.data, backgroundColor: '#612D53' }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: { min: 0, max: 5, title: { display: true, text: 'GPA (1.00 = Highest)' } },
                            y: { title: { display: true, text: 'Top Students' } }
                        }
                    }
                });
            });
        </script>
    @endpush
</x-app-layout>
