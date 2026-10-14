@extends('layouts.app')

@section('title', 'View Student - Student Grade Portal')

@section('content')
<div class="row">
    <div class="col-lg-10">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="page-title mb-0">Student Details</h2>
            <a href="{{ route('students.index') }}" class="btn btn-outline-secondary btn-sm">&larr; Back to List</a>
        </div>

        <div class="card shadow-sm border-0 mb-4">
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
                        <td>
                            {{ $student->enrolled_units }}
                            <span class="badge bg-{{ $student->load_status_color }} {{ $student->load_status_color === 'warning' ? 'text-dark' : '' }} ms-2">{{ $student->load_status }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th class="detail-label">Minimum Units</th>
                        <td>{{ $student->min_units }}</td>
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

                <div class="d-flex justify-content-between mt-4 flex-wrap gap-2">
                    <div class="d-flex gap-2">
                        <a href="{{ route('students.edit', $student) }}" class="btn btn-primary fw-semibold">Edit Record</a>
                        <a href="{{ route('transactions.create', ['student_id' => $student->id]) }}" class="btn btn-outline-primary fw-semibold">Record Transaction</a>
                    </div>

                    <form action="{{ route('students.destroy', $student) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('Are you sure you want to delete this student record?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete Record</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Lab 6 Part 9: transaction history with running balance + date filter --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h6 class="mb-0 fw-bold" style="color: var(--theme-text);">Unit Transaction History</h6>

                <form method="GET" action="{{ route('students.show', $student) }}" class="d-flex align-items-center gap-2 flex-wrap">
                    <label for="from" class="small text-muted mb-0">From</label>
                    <input type="date" id="from" name="from" value="{{ $from }}" class="form-control form-control-sm" style="width: 150px;">
                    <label for="to" class="small text-muted mb-0">To</label>
                    <input type="date" id="to" name="to" value="{{ $to }}" class="form-control form-control-sm" style="width: 150px;">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    @if($from || $to)
                        <a href="{{ route('students.show', $student) }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                    @endif
                </form>
            </div>
            <div class="card-body p-0">
                @if($errors->any())
                    <div class="alert alert-danger border-0 m-3">{{ $errors->first() }}</div>
                @endif
                <div class="table-responsive">
                    <table class="table table-theme table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="py-3 px-3">Date/Time</th>
                                <th class="py-3">Type</th>
                                <th class="py-3">Change</th>
                                <th class="py-3">Balance After</th>
                                <th class="py-3">Reference</th>
                                <th class="py-3">By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($history as $t)
                                <tr>
                                    <td class="px-3 text-nowrap">{{ $t->created_at->format('M d, Y h:i A') }}</td>
                                    <td><span class="badge {{ $t->type_badge }}">{{ $t->type_label }}</span></td>
                                    <td class="fw-bold {{ $t->quantity > 0 ? 'text-success' : 'text-danger' }}">{{ $t->signed_quantity }}</td>
                                    <td class="fw-semibold">{{ $t->balance_after }}</td>
                                    <td><a href="{{ route('transactions.show', $t) }}">{{ $t->reference_number ?? 'View' }}</a></td>
                                    <td>{{ $t->user?->name ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center py-4 text-muted">No transactions found for this student.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
