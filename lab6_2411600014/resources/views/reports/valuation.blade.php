@extends('layouts.app')

@section('title', 'Valuation Report - Student Grade Portal')

@section('content')
<h2 class="page-title mb-3">Reports</h2>
@include('reports._tabs')

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger border-0 py-2">{{ $errors->first() }}</div>
        @endif
        <form method="GET" action="{{ route('reports.valuation') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1" for="rate">Tuition rate per unit (₱)</label>
                <input type="number" step="0.01" min="0" id="rate" name="rate" value="{{ $rate }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-9 d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary btn-sm px-3">Recalculate</button>
                <a href="{{ route('reports.valuation', ['rate' => $rate, 'export' => 'csv']) }}" class="btn btn-success btn-sm px-3">Export CSV</a>
                <button type="button" class="btn btn-outline-secondary btn-sm px-3" onclick="window.print()">Print</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4 text-center">
    <div class="col-md-6"><div class="card stat-card border-primary-custom"><div class="card-body"><div class="card-title text-muted fw-bold">Total Enrolled Units</div><div class="card-text fw-bold">{{ number_format($totalUnits) }}</div></div></div></div>
    <div class="col-md-6"><div class="card stat-card border-success-custom"><div class="card-body"><div class="card-title text-muted fw-bold">Total Assessed Value</div><div class="card-text fw-bold text-success">₱{{ number_format($totalValue, 2) }}</div></div></div></div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold" style="color: var(--theme-text);">By Course</h6></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-theme table-hover align-middle mb-0">
                <thead><tr><th class="py-3 px-3">Course</th><th class="py-3">Students</th><th class="py-3">Units</th><th class="py-3 text-end pe-3">Assessed Value</th></tr></thead>
                <tbody>
                    @foreach($byCourse as $course => $row)
                        <tr>
                            <td class="px-3">{{ $course }}</td>
                            <td>{{ $row['students'] }}</td>
                            <td>{{ number_format($row['units']) }}</td>
                            <td class="text-end pe-3">₱{{ number_format($row['value'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold" style="color: var(--theme-text);">By Student</h6></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-theme table-hover align-middle mb-0">
                <thead><tr><th class="py-3 px-3">Student ID</th><th class="py-3">Name</th><th class="py-3">Course</th><th class="py-3">Units</th><th class="py-3 text-end pe-3">Assessed Value</th></tr></thead>
                <tbody>
                    @foreach($students as $s)
                        <tr>
                            <td class="px-3 fw-bold">{{ $s->student_id }}</td>
                            <td>{{ $s->name }}</td>
                            <td>{{ $s->course }}</td>
                            <td>{{ $s->enrolled_units }}</td>
                            <td class="text-end pe-3">₱{{ number_format($s->enrolled_units * $rate, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
