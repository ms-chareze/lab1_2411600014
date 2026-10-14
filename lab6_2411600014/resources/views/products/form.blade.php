@extends('layouts.app')

@section('title', isset($product) ? 'Edit Product - Inventory System' : 'Add Product - Inventory System')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header">
                {{ isset($product) ? 'Edit Product: ' . $product->name : 'Add New Product' }}
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ isset($product) ? route('products.update', $product) : route('products.store') }}" method="POST">
                    @csrf
                    @if(isset($product))
                        @method('PUT')
                    @endif

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="sku" class="form-label fw-bold">SKU</label>
                            <input type="text" class="form-control" id="sku" name="sku" maxlength="50" value="{{ old('sku', $product->sku ?? '') }}" required>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label for="name" class="form-label fw-bold">Product Name</label>
                            <input type="text" class="form-control" id="name" name="name" maxlength="255" value="{{ old('name', $product->name ?? '') }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="category" class="form-label fw-bold">Category</label>
                        <input type="text" class="form-control" id="category" name="category" list="category-list" maxlength="100"
                               value="{{ old('category', $product->category ?? '') }}" required>
                        <datalist id="category-list">
                            @foreach($categories as $c)
                                <option value="{{ $c }}">
                            @endforeach
                        </datalist>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-bold">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" maxlength="2000">{{ old('description', $product->description ?? '') }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="price" class="form-label fw-bold">Unit Price (&#8369;)</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="price" name="price" value="{{ old('price', $product->price ?? '') }}" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            @if(isset($product))
                                <label class="form-label fw-bold">Current Quantity</label>
                                <input type="text" class="form-control" value="{{ $product->quantity }}" readonly disabled>
                                <div class="form-text">
                                    Stock changes through
                                    <a href="{{ route('transactions.create', ['product_id' => $product->id]) }}">transactions</a>
                                    or <a href="{{ route('adjustments.create', ['product_id' => $product->id]) }}">adjustments</a>.
                                </div>
                            @else
                                <label for="initial_quantity" class="form-label fw-bold">Opening Stock</label>
                                <input type="number" min="0" class="form-control" id="initial_quantity" name="initial_quantity" value="{{ old('initial_quantity', 0) }}">
                                <div class="form-text">Recorded as a "Stock In" transaction.</div>
                            @endif
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="reorder_level" class="form-label fw-bold">Reorder Level</label>
                            <input type="number" min="0" class="form-control" id="reorder_level" name="reorder_level" value="{{ old('reorder_level', $product->reorder_level ?? 10) }}" required>
                            <div class="form-text">Alert when quantity is at or below this.</div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ isset($product) ? route('products.show', $product) : route('products.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">{{ isset($product) ? 'Update Product' : 'Save Product' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
