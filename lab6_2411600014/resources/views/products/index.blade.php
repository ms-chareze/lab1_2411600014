@extends('layouts.app')

@section('title', 'Products - Inventory System')

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="fw-bold text-portal">Products</h2>
    </div>
    <div class="col-md-6 text-md-end">
        @can('manage-stock')
            <a href="{{ route('products.create') }}" class="btn btn-primary">+ Add Product</a>
        @endcan
    </div>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('products.index') }}" class="card card-body shadow-sm mb-4">
    <div class="row g-2 align-items-end">
        <div class="col-md-4">
            <label for="q" class="form-label small fw-bold">Search</label>
            <input type="text" class="form-control" id="q" name="q" value="{{ request('q') }}" placeholder="Name or SKU">
        </div>
        <div class="col-md-3">
            <label for="category" class="form-label small fw-bold">Category</label>
            <select class="form-select" id="category" name="category">
                <option value="">All categories</option>
                @foreach($categories as $c)
                    <option value="{{ $c }}" @selected(request('category') === $c)>{{ $c }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label for="status" class="form-label small fw-bold">Stock status</label>
            <select class="form-select" id="status" name="status">
                <option value="">Any</option>
                <option value="in"  @selected(request('status') === 'in')>In Stock</option>
                <option value="low" @selected(request('status') === 'low')>Low Stock</option>
                <option value="out" @selected(request('status') === 'out')>Out of Stock</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-fill">Filter</button>
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </div>
</form>

<div class="card shadow-sm">
    <div class="card-header">Product Catalog ({{ $products->total() }})</div>
    <div class="card-body">
        @if($products->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th class="text-end">Price</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Reorder</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                            <tr>
                                <td class="fw-bold">{{ $product->sku }}</td>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->category }}</td>
                                <td class="text-end">&#8369;{{ number_format($product->price, 2) }}</td>
                                <td class="text-end fw-bold">{{ $product->quantity }}</td>
                                <td class="text-end">{{ $product->reorder_level }}</td>
                                <td><span class="badge bg-{{ $product->stock_status_color }}">{{ $product->stock_status }}</span></td>
                                <td class="text-center text-nowrap">
                                    <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-info text-white">View</a>
                                    @can('manage-stock')
                                        <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-warning text-white">Edit</a>
                                    @endcan
                                    @can('admin')
                                        <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Delete this product? Its transaction history will be kept.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $products->links('pagination::bootstrap-5') }}
        @else
            <div class="text-center py-4">
                <p class="text-muted mb-3">No products match your filters.</p>
                @can('manage-stock')
                    <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm">Add a Product</a>
                @endcan
            </div>
        @endif
    </div>
</div>
@endsection
