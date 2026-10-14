<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="fw-bold mb-0" style="color: var(--theme-text);" id="dashboardGreeting">Welcome, {{ Auth::user()->name }}!</h2>
            <div class="d-flex gap-2">
                <a href="{{ route('transactions.create') }}" class="btn btn-primary btn-sm fw-semibold">+ Record Transaction</a>
                <a href="{{ route('students.create') }}" class="btn btn-outline-primary btn-sm fw-semibold">+ Add New Student</a>
                <a href="{{ route('students.index') }}" class="btn btn-outline-primary btn-sm fw-semibold">View All Students</a>
            </div>
        </div>
    </x-slot>

    <!-- Live status bar (Lab 6 Part 7) -->
    <div class="d-flex justify-content-end align-items-center gap-2 mb-3 small">
        <span id="pollStatus" class="badge bg-secondary">Connecting...</span>
        <span class="text-muted">Last Updated: <strong id="lastUpdated">{{ now()->format('M d, Y h:i:s A') }}</strong></span>
        <button type="button" id="refreshNow" class="btn btn-outline-primary btn-sm py-0">Refresh now</button>
    </div>

    <!-- Metric cards (real database values; IDs are targeted by dashboard-poll.js) -->
    <div class="row text-center mb-4 g-3">
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card stat-card shadow-sm border-0 bg-white border-primary-custom h-100">
                <div class="card-body">
                    <div class="card-title text-muted fw-bold">Total Students</div>
                    <div class="card-text fw-bold text-primary-custom" id="stat-total-students">{{ $totalStudents }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card stat-card shadow-sm border-0 bg-white border-success-custom h-100">
                <div class="card-body">
                    <div class="card-title text-muted fw-bold">Average GPA</div>
                    <div class="card-text fw-bold text-success" id="stat-average-gpa">{{ number_format($averageGpa, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card stat-card shadow-sm border-0 bg-white border-accent h-100">
                <div class="card-body">
                    <div class="card-title text-muted fw-bold">Academic Warnings</div>
                    <div class="card-text fw-bold text-danger" id="stat-warning-count">{{ $warningCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card stat-card shadow-sm border-0 bg-white border-primary-custom h-100">
                <div class="card-body">
                    <div class="card-title text-muted fw-bold">Total Units</div>
                    <div class="card-text fw-bold" style="color: var(--theme-text);" id="stat-total-units">{{ number_format($totalUnits) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card stat-card shadow-sm border-0 bg-white border-accent h-100">
                <div class="card-body">
                    <div class="card-title text-muted fw-bold">Low Unit Load</div>
                    <div class="card-text fw-bold text-warning" id="stat-low-load">{{ $lowLoadCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card stat-card shadow-sm border-0 bg-white border-success-custom h-100">
                <div class="card-body">
                    <div class="card-title text-muted fw-bold">Transactions Today</div>
                    <div class="card-text fw-bold text-primary-custom" id="stat-transactions-today">{{ $transactionsToday }}</div>
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

    <!-- Lab 6: Low unit load alerts (refreshed by AJAX) -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold" style="color: var(--theme-text);">
                Low Unit Load Alerts
                <span id="lowLoadBadge" class="badge bg-warning text-dark ms-2">{{ $lowLoadStudents->count() }}</span>
            </h6>
            <a href="{{ route('reports.low-load') }}" class="small fw-semibold">View report</a>
        </div>
        <ul class="list-group list-group-flush" id="lowLoadList">
            @forelse($lowLoadStudents as $s)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>
                        <a href="{{ route('students.show', $s) }}" class="fw-bold text-decoration-none">{{ $s->name }}</a>
                        <span class="text-muted small">({{ $s->student_id }})</span>
                    </span>
                    <span>
                        <span class="small text-muted me-2">{{ $s->enrolled_units }} / min {{ $s->min_units }} units</span>
                        <span class="badge bg-{{ $s->load_status_color }} {{ $s->load_status_color === 'warning' ? 'text-dark' : '' }}">{{ $s->load_status }}</span>
                    </span>
                </li>
            @empty
                <li class="list-group-item text-center text-muted py-3">All students have a healthy unit load.</li>
            @endforelse
        </ul>
    </div>

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

    <!-- Lab 6: Recent unit transactions (refreshed by AJAX) -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold" style="color: var(--theme-text);">Recent Transactions</h6>
            <a href="{{ route('transactions.index') }}" class="small fw-semibold">View all</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-theme table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="py-3 px-3">Date/Time</th>
                            <th class="py-3">Student</th>
                            <th class="py-3">Type</th>
                            <th class="py-3">Units</th>
                            <th class="py-3">Reference</th>
                            <th class="py-3">By</th>
                            <th class="py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="recentTransactionsBody">
                        @forelse($recentTransactions as $t)
                            <tr>
                                <td class="px-3 text-nowrap">{{ $t->created_at->format('M d, Y h:i A') }}</td>
                                <td><a href="{{ route('students.show', $t->student_id) }}">{{ $t->student?->name }}</a></td>
                                <td><span class="badge {{ $t->type_badge }}">{{ $t->type_label }}</span></td>
                                <td class="fw-bold {{ $t->quantity > 0 ? 'text-success' : 'text-danger' }}">{{ $t->signed_quantity }}</td>
                                <td>{{ $t->reference_number ?? '—' }}</td>
                                <td>{{ $t->user?->name ?? '—' }}</td>
                                <td class="text-center"><a href="{{ route('transactions.show', $t) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-4 text-muted">No transactions recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
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

        <!-- Lab 6: AJAX polling (public/js/dashboard-poll.js) -->
        <script>
            window.DashboardConfig = {
                statsUrl: @json(route('api.dashboard.stats')),
                lowLoadUrl: @json(route('api.students.low-load')),
                recentUrl: @json(route('api.transactions.recent')),
                loginUrl: @json(route('login')),
                intervalMs: 30000
            };
        </script>
        <script src="{{ asset('js/dashboard-poll.js') }}"></script>
    @endpush
</x-app-layout>
