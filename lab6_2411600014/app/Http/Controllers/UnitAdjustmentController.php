<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidUnitMovementException;
use App\Models\EnrollmentTransaction;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Lab 6 Part 8: reconcile the system count with the registrar's "physical" count. */
class UnitAdjustmentController extends Controller
{
    public function create(): View
    {
        Gate::authorize('adjust', EnrollmentTransaction::class);

        $students = Student::orderBy('name')->get(['id', 'student_id', 'name', 'enrolled_units']);
        $reasons = EnrollmentTransaction::ADJUSTMENT_REASONS;

        return view('adjustments.create', compact('students', 'reasons'));
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('adjust', EnrollmentTransaction::class);

        $data = $request->validate([
            'student_id'     => ['required', 'integer', 'exists:students,id'],
            'physical_count' => ['required', 'integer', 'min:0', 'max:' . Student::MAX_UNITS],
            'reason'         => ['required', Rule::in(array_keys(EnrollmentTransaction::ADJUSTMENT_REASONS))],
            'notes'          => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $transaction = DB::transaction(function () use ($data, $request) {
                // Lock the row so the difference is calculated on the latest value.
                $student = Student::query()->lockForUpdate()->findOrFail($data['student_id']);

                $difference = (int) $data['physical_count'] - $student->enrolled_units;

                if ($difference === 0) {
                    throw new InvalidUnitMovementException(
                        'The physical count matches the system record. No adjustment is needed.'
                    );
                }

                // The observer applies the difference to the student.
                return EnrollmentTransaction::create([
                    'student_id'       => $student->id,
                    'user_id'          => $request->user()->id,
                    'type'             => EnrollmentTransaction::TYPE_ADJUSTMENT,
                    'quantity'         => $difference,
                    'reference_number' => 'ADJ-' . now()->format('Ymd-His'),
                    'reason'           => $data['reason'],
                    'notes'            => $data['notes'] ?? null,
                ]);
            });
        } catch (InvalidUnitMovementException $e) {
            return back()->withInput()->withErrors(['physical_count' => $e->getMessage()]);
        }

        return redirect()->route('transactions.show', $transaction)
            ->with('success', "Adjustment recorded ({$transaction->signed_quantity} unit(s)).");
    }
}
