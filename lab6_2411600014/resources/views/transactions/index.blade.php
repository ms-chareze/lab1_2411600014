@extends('layouts.app')

@section('title', 'Transactions - Student Grade Portal')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h2 class="page-title mb-0">Unit Transactions</h2>
    <a href="{{ route('transactions.create') }}" class="btn btn-primary btn-sm fw-semibold">+ Record Transaction</a>
</div>

{{-- Filter form --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger border-0 py-2">{{ $errors->first() }}</div>
        @endif
        <form method="GET" action="{{ route('transactions.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label for="student_id" class="form-label small mb-1">Student</label>
                <select name="student_id" id="student_id" class="form-select form-select-sm">
                    <option value="">All students</option>
                    @foreach($students as $s)
                        <option value="{{ $s->id }}" @selected(request('student_id') == $s->id)>{{ $s->student_id }} - {{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="type" class="form-label small mb-1">Type</label>
                <select name="type" id="type" class="form-select form-select-sm">
                    <option value="">All types</option>
                    @foreach($types as $value => $label)
                        <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="date_from" class="form-label small mb-1">From</label>
                <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-2">
                <label for="date_to" class="form-label small mb-1">To</label>
                <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm px-3">Filter</button>
                <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary btn-sm px-3">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-theme table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="py-3 px-3">Date/Time</th>
                        <th class="py-3">Student</th>
                        <th class="py-3">Type</th>
                        <th class="py-3">Quantity</th>
                        <th class="py-3">Reference No.</th>
                        <th class="py-3">Performed By</th>
                        <th class="py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $t)
                        <tr>
                            <td class="px-3 text-nowrap">{{ $t->created_at->format('M d, Y h:i A') }}</td>
                            <td>
                                <a href="{{ route('students.show', $t->student_id) }}" class="fw-semibold">{{ $t->student?->name ?? 'Deleted student' }}</a>
                                <div class="small text-muted">{{ $t->student?->student_id }}</div>
                            </td>
                            <td><span class="badge {{ $t->type_badge }}">{{ $t->type_label }}</span></td>
                            <td class="fw-bold {{ $t->quantity > 0 ? 'text-success' : 'text-danger' }}">{{ $t->signed_quantity }}</td>
                            <td>{{ $t->reference_number ?? '—' }}</td>
                            <td>{{ $t->user?->name ?? '—' }}</td>
                            <td class="text-center text-nowrap">
                                <a href="{{ route('transactions.show', $t) }}" class="btn btn-sm btn-outline-primary">View</a>
                                @can('delete', $t)
                                    <form action="{{ route('transactions.destroy', $t) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Delete this transaction? The unit change will be reversed.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-5 text-muted">No transactions found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($transactions->hasPages())
        <div class="card-footer bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="small text-muted">Showing {{ $transactions->firstItem() }}-{{ $transactions->lastItem() }} of {{ $transactions->total() }}</span>
            {{ $transactions->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
