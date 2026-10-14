@extends('layouts.app')

@section('title', 'Reports - Inventory System')

@section('content')
@php
    $tabs = ['movement' => 'Stock Movement', 'low-stock' => 'Low Stock', 'valuation' => 'Inventory Valuation'];
    $exportQuery = array_filter(request()->query(), fn ($v) => $v !== null && $v !== '');
    $exportQuery['report'] = $filters['report'];
    $numericCols = ['Quantity', 'Before', 'After', 'Reorder Level', 'Suggested Order', 'Unit Price', 'Stock Value'];
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h2 class="fw-bold text-portal mb-0">Inventory Reports</h2>
    <a href="{{ route('reports.export', $exportQuery) }}" class="btn btn-outline-dark btn-sm">&#11015; Export CSV</a>
</div>

<ul class="nav nav-tabs mb-3">
    @foreach($tabs as $key => $label)
        <li class="nav-item">
            <a class="nav-link {{ $filters['report'] === $key ? 'active fw-semibold' : '' }}"
               href="{{ route('reports.index', ['report' => $key]) }}">{{ $label }}</a>
        </li>
    @endforeach
</ul>

@if($filters['report'] === 'movement')
    <form method="GET" action="{{ route('reports.index') }}" class="card card-body shadow-sm mb-4">
        <input type="hidden" name="report" value="movement">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label for="from" class="form-label small fw-bold">From</label>
                <input type="date" class="form-control" id="from" name="from" value="{{ $filters['from'] }}">
            </div>
            <div class="col-md-3">
                <label for="to" class="form-label small fw-bold">To</label>
                <input type="date" class="form-control" id="to" name="to" value="{{ $filters['to'] }}">
            </div>
            <div class="col-md-3">
                <label for="product_id" class="form-label small fw-bold">Product</label>
                <select class="form-select" id="product_id" name="product_id">
                    <option value="">All products</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" @selected((string) $filters['product_id'] === (string) $p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="type" class="form-label small fw-bold">Type</label>
                <select class="form-select" id="type" name="type">
                    <option value="">All</option>
                    @foreach($types as $value => $label)
                        <option value="{{ $value }}" @selected($filters['type'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100">Run</button>
            </div>
        </div>
    </form>
@endif

<div class="card shadow-sm">
    <div class="card-header">{{ $report['title'] }}</div>
    <div class="card-body">
        <div class="row g-3 mb-3">
            @foreach($report['summary'] as $label => $value)
                <div class="col-sm-6 col-lg-3">
                    <div class="border rounded p-2 bg-light">
                        <div class="small text-muted text-uppercase fw-bold">{{ $label }}</div>
                        <div class="fs-5 fw-bold text-portal">{{ is_int($value) ? number_format($value) : $value }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        @if(count($report['rows']) > 0)
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle">
                    <thead>
                        <tr>
                            @foreach($report['columns'] as $col)
                                <th class="{{ in_array($col, $numericCols, true) ? 'text-end' : '' }}">{{ $col }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report['rows'] as $row)
                            <tr>
                                @foreach($row as $i => $cell)
                                    <td class="{{ in_array($report['columns'][$i], $numericCols, true) ? 'text-end' : '' }}">{{ $cell }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($report['truncated'])
                <div class="alert alert-info py-2 mb-0">Showing the first 500 rows. Export CSV to get everything.</div>
            @endif
        @else
            <p class="text-muted text-center py-4 mb-0">No data for this report.</p>
        @endif
    </div>
</div>
@endsection
