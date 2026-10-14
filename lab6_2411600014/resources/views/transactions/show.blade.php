@extends('layouts.app')

@section('title', 'Transaction Details - Student Grade Portal')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="page-title mb-0">Transaction #{{ $transaction->id }}</h2>
    <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary btn-sm">&larr; Back to List</a>
</div>

<div class="row g-3">
    {{-- Transaction details --}}
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-gradient-primary text-white py-3"><h6 class="mb-0 fw-bold">Transaction Details</h6></div>
            <div class="card-body">
                <table class="table table-bordered align-middle mb-0">
                    <tr><th class="detail-label">Type</th><td><span class="badge {{ $transaction->type_badge }}">{{ $transaction->type_label }}</span></td></tr>
                    <tr><th class="detail-label">Quantity</th>
                        <td class="fw-bold {{ $transaction->quantity > 0 ? 'text-success' : 'text-danger' }}">{{ $transaction->signed_quantity }} unit(s)</td></tr>
                    <tr><th class="detail-label">Reference No.</th><td>{{ $transaction->reference_number ?? '—' }}</td></tr>
                    @if($transaction->reason)
                        <tr><th class="detail-label">Reason</th><td>{{ $transaction->reason }}</td></tr>
                    @endif
                    <tr><th class="detail-label">Notes</th><td>{{ $transaction->notes ?? '—' }}</td></tr>
                    <tr><th class="detail-label">Recorded</th><td>{{ $transaction->created_at->format('F d, Y h:i A') }}</td></tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        {{-- Student (product) info --}}
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold" style="color: var(--theme-text);">Student Information</h6></div>
            <div class="card-body">
                @if($transaction->student)
                    <p class="mb-1 fw-bold">
                        <a href="{{ route('students.show', $transaction->student) }}">{{ $transaction->student->name }}</a>
                        <span class="text-muted fw-normal">({{ $transaction->student->student_id }})</span>
                    </p>
                    <p class="mb-1 small">{{ $transaction->student->course }}</p>
                    <p class="mb-0 small">
                        Current load: <strong>{{ $transaction->student->enrolled_units }}</strong> unit(s)
                        <span class="badge bg-{{ $transaction->student->load_status_color }} {{ $transaction->student->load_status_color === 'warning' ? 'text-dark' : '' }} ms-1">{{ $transaction->student->load_status }}</span>
                    </p>
                @else
                    <p class="text-muted mb-0">The student record no longer exists.</p>
                @endif
            </div>
        </div>

        {{-- User info (accountability) --}}
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3"><h6 class="mb-0 fw-bold" style="color: var(--theme-text);">Performed By</h6></div>
            <div class="card-body">
                @if($transaction->user)
                    <p class="mb-1 fw-bold">{{ $transaction->user->name }}</p>
                    <p class="mb-0 small text-muted">{{ $transaction->user->email }}</p>
                @else
                    <p class="text-muted mb-0">The staff account was removed.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between mt-4">
    <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary">Back to List</a>
    @can('delete', $transaction)
        <form action="{{ route('transactions.destroy', $transaction) }}" method="POST"
              onsubmit="return confirm('Delete this transaction? The unit change will be reversed.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Delete Transaction</button>
        </form>
    @endcan
</div>
@endsection
