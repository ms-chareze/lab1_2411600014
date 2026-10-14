@extends('layouts.app')

@section('title', 'Unit Adjustment - Student Grade Portal')

@push('head')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
@endpush

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-gradient-primary text-white py-3">
                <h5 class="mb-0 fw-bold">Unit Adjustment (Physical Count)</h5>
            </div>
            <div class="card-body p-4">
                <p class="text-muted small">
                    Use this when the registrar's count does not match the system. The system calculates the
                    difference and logs an adjustment transaction.
                </p>

                @if ($errors->any())
                    <div class="alert alert-danger border-0">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('adjustments.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="student_id" class="form-label">Student</label>
                        <select id="student_id" name="student_id" class="form-select @error('student_id') is-invalid @enderror" required>
                            <option value="">Search for a student...</option>
                            @foreach($students as $s)
                                <option value="{{ $s->id }}" @selected(old('student_id') == $s->id)>{{ $s->student_id }} - {{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="system_units" class="form-label">Current System Units</label>
                            <input type="text" id="system_units" class="form-control bg-light" value="" placeholder="Select a student" readonly>
                            <div id="loadError" class="form-text text-danger d-none"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="physical_count" class="form-label">Physical Count (units)</label>
                            <input type="number" class="form-control @error('physical_count') is-invalid @enderror" id="physical_count" name="physical_count"
                                   min="0" max="{{ App\Models\Student::MAX_UNITS }}" value="{{ old('physical_count') }}" required>
                            <div id="differenceText" class="form-text"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason for Adjustment</label>
                        <select id="reason" name="reason" class="form-select @error('reason') is-invalid @enderror" required>
                            <option value="">Select reason</option>
                            @foreach($reasons as $value => $label)
                                <option value="{{ $value }}" @selected(old('reason') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="notes" class="form-label">Notes <span class="text-muted fw-normal">(optional)</span></label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" maxlength="1000">{{ old('notes') }}</textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary fw-semibold">Post Adjustment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // URL template for the JSON endpoint (AJAX load of current units)
            const unitsUrl = @json(route('api.students.units', ['student' => '__ID__']));

            const select = document.getElementById('student_id');
            const systemUnits = document.getElementById('system_units');
            const physical = document.getElementById('physical_count');
            const diffText = document.getElementById('differenceText');
            const loadError = document.getElementById('loadError');
            let currentUnits = null;

            new TomSelect(select, { create: false, maxOptions: 500 });

            function updateDifference() {
                if (currentUnits === null || physical.value === '') { diffText.textContent = ''; return; }
                const diff = parseInt(physical.value, 10) - currentUnits;
                if (diff === 0) {
                    diffText.textContent = 'No difference - nothing to adjust.';
                    diffText.className = 'form-text text-muted';
                } else {
                    diffText.textContent = `Difference: ${diff > 0 ? '+' : ''}${diff} unit(s) (physical - system)`;
                    diffText.className = 'form-text fw-semibold ' + (diff > 0 ? 'text-success' : 'text-danger');
                }
            }

            async function loadUnits() {
                loadError.classList.add('d-none');
                currentUnits = null;

                if (!select.value) { systemUnits.value = ''; updateDifference(); return; }

                systemUnits.value = 'Loading...';
                try {
                    const res = await fetch(unitsUrl.replace('__ID__', select.value), {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin'
                    });
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    const json = await res.json();
                    currentUnits = json.data.enrolled_units;
                    systemUnits.value = currentUnits + ' unit(s)';
                } catch (e) {
                    systemUnits.value = '';
                    loadError.textContent = 'Could not load the current units. Please try again.';
                    loadError.classList.remove('d-none');
                }
                updateDifference();
            }

            select.addEventListener('change', loadUnits);
            physical.addEventListener('input', updateDifference);
            if (select.value) loadUnits();
        });
    </script>
@endpush
