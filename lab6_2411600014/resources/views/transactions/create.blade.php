@extends('layouts.app')

@section('title', 'Record Transaction - Student Grade Portal')

@push('head')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
@endpush

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-gradient-primary text-white py-3">
                <h5 class="mb-0 fw-bold">Record Unit Transaction</h5>
            </div>
            <div class="card-body p-4">
                @if ($errors->any())
                    <div class="alert alert-danger border-0">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('transactions.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="student_id" class="form-label">Student</label>
                        <select id="student_id" name="student_id" class="form-select @error('student_id') is-invalid @enderror" required>
                            <option value="">Search for a student...</option>
                            @foreach($students as $s)
                                <option value="{{ $s->id }}"
                                        data-units="{{ $s->enrolled_units }}"
                                        data-min="{{ $s->min_units }}"
                                        @selected(old('student_id', request('student_id')) == $s->id)>
                                    {{ $s->student_id }} - {{ $s->name }}
                                </option>
                            @endforeach
                        </select>
                        <div id="stockInfo" class="form-text d-none"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label d-block">Transaction Type</label>
                        @foreach($types as $value => $label)
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="type" id="type_{{ $value }}" value="{{ $value }}"
                                       @checked(old('type', 'add') === $value) required>
                                <label class="form-check-label" for="type_{{ $value }}">{{ $label }}</label>
                            </div>
                        @endforeach
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="quantity" class="form-label">Quantity (units)</label>
                            <input type="number" class="form-control @error('quantity') is-invalid @enderror" id="quantity" name="quantity"
                                   min="1" max="{{ App\Models\Student::MAX_UNITS }}" value="{{ old('quantity') }}" required>
                            <div id="quantityHint" class="form-text"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="reference_number" class="form-label">Reference Number <span class="text-muted fw-normal">(optional)</span></label>
                            <input type="text" class="form-control @error('reference_number') is-invalid @enderror" id="reference_number" name="reference_number"
                                   maxlength="100" value="{{ old('reference_number') }}" placeholder="e.g. ENR-2026-0001">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="notes" class="form-label">Notes <span class="text-muted fw-normal">(optional)</span></label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3" maxlength="1000">{{ old('notes') }}</textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" id="submitBtn" class="btn btn-primary fw-semibold">Save Transaction</button>
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
            const MAX_UNITS = @json(App\Models\Student::MAX_UNITS);
            const select = document.getElementById('student_id');
            const qty = document.getElementById('quantity');
            const info = document.getElementById('stockInfo');
            const hint = document.getElementById('quantityHint');
            const submit = document.getElementById('submitBtn');

            // Searchable dropdown (Tom Select = Select2 alternative without jQuery)
            new TomSelect(select, { create: false, maxOptions: 500 });

            function selectedType() {
                const checked = document.querySelector('input[name="type"]:checked');
                return checked ? checked.value : 'add';
            }

            // Show current stock level + dynamic max quantity
            function refresh() {
                const opt = select.options[select.selectedIndex];

                if (!opt || !opt.value) {
                    info.classList.add('d-none');
                    hint.textContent = '';
                    qty.max = MAX_UNITS;
                    submit.disabled = false;
                    return;
                }

                const units = parseInt(opt.dataset.units, 10);
                const min = parseInt(opt.dataset.min, 10);
                const status = units <= 0 ? 'No Load' : (units <= min ? 'Low Load' : 'Normal Load');

                info.textContent = `Current load: ${units} unit(s) | Minimum: ${min} | Status: ${status}`;
                info.classList.remove('d-none');

                const max = selectedType() === 'drop' ? units : MAX_UNITS - units;
                qty.min = 1;
                qty.max = Math.max(max, 0);

                hint.textContent = selectedType() === 'drop'
                    ? `You can drop at most ${units} unit(s).`
                    : `You can add at most ${Math.max(max, 0)} unit(s) (limit ${MAX_UNITS}).`;

                submit.disabled = max < 1;
            }

            select.addEventListener('change', refresh);
            document.querySelectorAll('input[name="type"]').forEach(r => r.addEventListener('change', refresh));
            refresh();
        });
    </script>
@endpush
