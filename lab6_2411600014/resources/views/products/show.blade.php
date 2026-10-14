@extends('layouts.app')

@section('title', $product->name . ' - Inventory System')

@section('content')
<div class="row mb-3">
    <div class="col-md-6">
        <h2 class="fw-bold text-portal">{{ $product->name }}</h2>
        <span class="text-muted">{{ $product->sku }} &middot; {{ $product->category }}</span>
    </div>
    <div class="col-md-6 text-md-end">
        <a href="{{ route('products.index') }}" class="btn btn-secondary btn-sm">&larr; Back to List</a>
    </div>
</div>

<div class="row g-4 mb-4">
    {{-- Product details --}}
    <div class="col-lg-7">
        <div class="card shadow-sm h-100">
            <div class="card-header text-white" style="background-color: #612D53;">Product Details</div>
            <div class="card-body">
                <table class="table table-bordered align-middle mb-3">
                    <tr><th class="w-25 bg-light">SKU</th><td class="fw-bold">{{ $product->sku }}</td></tr>
                    <tr><th class="bg-light">Name</th><td>{{ $product->name }}</td></tr>
                    <tr><th class="bg-light">Category</th><td>{{ $product->category }}</td></tr>
                    <tr><th class="bg-light">Description</th><td>{{ $product->description ?: '-' }}</td></tr>
                    <tr><th class="bg-light">Unit Price</th><td>&#8369;{{ number_format($product->price, 2) }}</td></tr>
                    <tr><th class="bg-light">Stock Value</th><td>&#8369;{{ number_format($product->stock_value, 2) }}</td></tr>
                    <tr>
                        <th class="bg-light">Quantity</th>
                        <td>
                            <span class="fw-bold fs-5">{{ $product->quantity }}</span>
                            <span class="badge bg-{{ $product->stock_status_color }} ms-2">{{ $product->stock_status }}</span>
                            <span class="text-muted small ms-2">(reorder at {{ $product->reorder_level }})</span>
                        </td>
                    </tr>
                    <tr><th class="bg-light">Last Updated</th><td>{{ $product->updated_at->format('F d, Y h:i A') }}</td></tr>
                </table>

                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('transactions.create', ['product_id' => $product->id]) }}" class="btn btn-primary btn-sm">Record Transaction</a>
                    @can('manage-stock')
                        <a href="{{ route('adjustments.create', ['product_id' => $product->id]) }}" class="btn btn-outline-dark btn-sm">Adjust Stock</a>
                        <a href="{{ route('products.edit', $product) }}" class="btn btn-warning btn-sm text-white">Edit</a>
                    @endcan
                    @can('admin')
                        <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Delete this product? Its transaction history will be kept.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    {{-- Stock summary --}}
    <div class="col-lg-5">
        <div class="card shadow-sm h-100">
            <div class="card-header">Stock Level</div>
            <div class="card-body d-flex flex-column justify-content-center text-center">
                <div class="display-4 fw-bold text-{{ $product->stock_status_color }}">{{ $product->quantity }}</div>
                <div class="text-muted mb-3">units on hand</div>
                @php
                    $ceiling = max($product->reorder_level * 3, $product->quantity, 1);
                    $percent = min(100, round($product->quantity / $ceiling * 100));
                @endphp
                <div class="progress" role="progressbar" aria-label="Stock level" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar bg-{{ $product->stock_status_color }}" style="width: {{ $percent }}%"></div>
                </div>
                <div class="small text-muted mt-2">Reorder level: {{ $product->reorder_level }}</div>
                @if($product->low_stock_notified_at)
                    <div class="small text-muted mt-1">Low stock alert sent {{ $product->low_stock_notified_at->diffForHumans() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Transaction history with running balance (Part 9) --}}
<div class="card shadow-sm">
    <div class="card-header">Transaction History</div>
    <div class="card-body">
        <form method="GET" action="{{ route('products.show', $product) }}" class="row g-2 align-items-end mb-3">
            <div class="col-md-3">
                <label for="from" class="form-label small fw-bold">From</label>
                <input type="date" class="form-control" id="from" name="from" value="{{ request('from') }}">
            </div>
            <div class="col-md-3">
                <label for="to" class="form-label small fw-bold">To</label>
                <input type="date" class="form-control" id="to" name="to" value="{{ request('to') }}">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('products.show', $product) }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>

        @error('to')
            <div class="alert alert-danger py-2">{{ $message }}</div>
        @enderror

        @if($history->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Date/Time</th>
                            <th>Type</th>
                            <th class="text-end">Quantity</th>
                            <th class="text-end">Balance After</th>
                            <th>Reference</th>
                            <th>User</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($history as $t)
                            <tr>
                                <td>{{ $t->created_at->format('M d, Y h:i A') }}</td>
                                <td><span class="badge bg-{{ $t->type_color }}">{{ $t->type_label }}</span></td>
                                <td class="text-end {{ $t->quantity > 0 ? 'qty-plus' : 'qty-minus' }}">{{ $t->signed_quantity }}</td>
                                <td class="text-end fw-bold">{{ $t->quantity_after }}</td>
                                <td>{{ $t->reference_number ?: '-' }}</td>
                                <td>{{ $t->user->name }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $history->links('pagination::bootstrap-5') }}
        @else
            <p class="text-muted text-center py-3 mb-0">No transactions found for this product.</p>
        @endif
    </div>
</div>
@endsection
