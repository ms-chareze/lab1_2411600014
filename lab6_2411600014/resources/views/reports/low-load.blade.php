@extends('layouts.app')

@section('title', 'Low Unit Load Report - Student Grade Portal')

@section('content')
<h2 class="page-title mb-3">Reports</h2>
@include('reports._tabs')

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <p class="mb-0 text-muted">Students at or below their minimum unit load: <strong>{{ $students->count() }}</strong></p>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.low-load', ['export' => 'csv']) }}" class="btn btn-success btn-sm px-3">Export CSV</a>
        <button type="button" class="btn btn-outline-secondary btn-sm px-3" onclick="window.print()">Print</button>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-theme table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="py-3 px-3">Student ID</th><th class="py-3">Name</th><th class="py-3">Course</th>
                        <th class="py-3">Units</th><th class="py-3">Minimum</th><th class="py-3">Units To Add</th><th class="py-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $s)
                        <tr>
                            <td class="px-3 fw-bold">{{ $s->student_id }}</td>
                            <td><a href="{{ route('students.show', $s) }}">{{ $s->name }}</a></td>
                            <td>{{ $s->course }}</td>
                            <td>{{ $s->enrolled_units }}</td>
                            <td>{{ $s->min_units }}</td>
                            <td class="fw-semibold">{{ $s->min_units - $s->enrolled_units + 1 }}</td>
                            <td><span class="badge bg-{{ $s->load_status_color }} {{ $s->load_status_color === 'warning' ? 'text-dark' : '' }}">{{ $s->load_status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-4 text-muted">Every student is above the minimum load.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
