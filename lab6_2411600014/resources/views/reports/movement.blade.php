@extends('layouts.app')

@section('title', 'Stock Movement Report - Student Grade Portal')

@section('content')
<h2 class="page-title mb-3">Reports</h2>
@include('reports._tabs')

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger border-0 py-2">{{ $errors->first() }}</div>
        @endif
        <form method="GET" action="{{ route('reports.movement') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1" for="date_from">From</label>
                <input type="date" id="date_from" name="date_from" value="{{ $from }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1" for="date_to">To</label>
                <input type="date" id="date_to" name="date_to" value="{{ $to }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-6 d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary btn-sm px-3">Generate</button>
                <a href="{{ route('reports.movement', ['date_from' => $from, 'date_to' => $to, 'export' => 'csv']) }}" class="btn btn-success btn-sm px-3">Export CSV</a>
                <button type="button" class="btn btn-outline-secondary btn-sm px-3" onclick="window.print()">Print</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4 text-center">
    <div class="col-md-4"><div class="card stat-card border-success-custom"><div class="card-body"><div class="card-title text-muted fw-bold">Units Added</div><div class="card-text fw-bold text-success">+{{ $totalAdded }}</div></div></div></div>
    <div class="col-md-4"><div class="card stat-card border-accent"><div class="card-body"><div class="card-title text-muted fw-bold">Units Dropped</div><div class="card-text fw-bold text-danger">-{{ $totalDropped }}</div></div></div></div>
    <div class="col-md-4"><div class="card stat-card border-primary-custom"><div class="card-body"><div class="card-title text-muted fw-bold">Net Movement</div><div class="card-text fw-bold">{{ $totalAdded - $totalDropped >= 0 ? '+' : '' }}{{ $totalAdded - $totalDropped }}</div></div></div></div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-theme table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="py-3 px-3">Date/Time</th><th class="py-3">Student</th><th class="py-3">Type</th>
                        <th class="py-3">Quantity</th><th class="py-3">Reference</th><th class="py-3">Reason</th><th class="py-3">By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $t)
                        <tr>
                            <td class="px-3 text-nowrap">{{ $t->created_at->format('M d, Y h:i A') }}</td>
                            <td>{{ $t->student?->name }} <span class="small text-muted">({{ $t->student?->student_id }})</span></td>
                            <td><span class="badge {{ $t->type_badge }}">{{ $t->type_label }}</span></td>
                            <td class="fw-bold {{ $t->quantity > 0 ? 'text-success' : 'text-danger' }}">{{ $t->signed_quantity }}</td>
                            <td>{{ $t->reference_number ?? '—' }}</td>
                            <td>{{ $t->reason ?? '—' }}</td>
                            <td>{{ $t->user?->name ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-4 text-muted">No transactions in this date range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
