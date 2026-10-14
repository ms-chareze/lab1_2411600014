<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidUnitMovementException;
use App\Models\EnrollmentTransaction;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EnrollmentTransactionController extends Controller
{
    /** List transactions: paginated, eager loaded, filterable. */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'student_id' => ['nullable', 'integer'],
            'type'       => ['nullable', Rule::in(array_keys(EnrollmentTransaction::TYPES))],
            'date_from'  => ['nullable', 'date'],
            'date_to'    => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $transactions = EnrollmentTransaction::with(['student', 'user'])
            ->when($filters['student_id'] ?? null, fn ($q, $v) => $q->where('student_id', $v))
            ->when($filters['type'] ?? null, fn ($q, $v) => $q->where('type', $v))
            ->when($filters['date_from'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['date_to'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->recent()
            ->paginate(20)
            ->withQueryString();

        $students = Student::orderBy('name')->get(['id', 'student_id', 'name']);
        $types = EnrollmentTransaction::TYPES;

        return view('transactions.index', compact('transactions', 'students', 'types'));
    }

    public function create(): View
    {
        $students = Student::orderBy('name')->get();
        $types = EnrollmentTransaction::FORM_TYPES;

        return view('transactions.create', compact('students', 'types'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'student_id'       => ['required', 'integer', 'exists:students,id'],
            'type'             => ['required', Rule::in(array_keys(EnrollmentTransaction::FORM_TYPES))],
            'quantity'         => ['required', 'integer', 'min:1', 'max:' . Student::MAX_UNITS],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes'            => ['nullable', 'string', 'max:1000'],
        ], [
            'student_id.exists' => 'The selected student does not exist.',
            'quantity.min'      => 'Quantity must be at least 1 unit.',
        ]);

        // Custom "sufficient units" validation (after-validation hook).
        $validator->after(function ($validator) use ($request) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $student = Student::find($request->input('student_id'));
            $qty = (int) $request->input('quantity');

            if ($request->input('type') === EnrollmentTransaction::TYPE_DROP && $qty > $student->enrolled_units) {
                $validator->errors()->add(
                    'quantity',
                    "Not enough units: {$student->name} only has {$student->enrolled_units} enrolled unit(s)."
                );
            }

            if ($request->input('type') === EnrollmentTransaction::TYPE_ADD
                && $student->enrolled_units + $qty > Student::MAX_UNITS) {
                $validator->errors()->add(
                    'quantity',
                    "{$student->name} would exceed the " . Student::MAX_UNITS . "-unit maximum load."
                );
            }
        });

        $data = $validator->validate();

        $quantity = (int) $data['quantity'];
        $signed = $data['type'] === EnrollmentTransaction::TYPE_DROP ? -$quantity : $quantity;

        try {
            // Log row + student update succeed or fail together.
            DB::transaction(fn () => EnrollmentTransaction::create([
                'student_id'       => $data['student_id'],
                'user_id'          => $request->user()->id,
                'type'             => $data['type'],
                'quantity'         => $signed,
                'reference_number' => $data['reference_number'] ?? null,
                'notes'            => $data['notes'] ?? null,
            ]));
        } catch (InvalidUnitMovementException $e) {
            return back()->withInput()->withErrors(['quantity' => $e->getMessage()]);
        }

        $student = Student::find($data['student_id']);

        return redirect()->route('transactions.index')->with(
            'success',
            sprintf(
                'Transaction recorded: %s %d unit(s) for %s. Current load: %d unit(s).',
                $signed > 0 ? 'added' : 'dropped',
                abs($signed),
                $student->name,
                $student->enrolled_units
            )
        );
    }

    public function show(EnrollmentTransaction $transaction): View
    {
        $transaction->load(['student', 'user']);

        return view('transactions.show', compact('transaction'));
    }

    /** Admin only. Deleting reverses the unit change (if still valid). */
    public function destroy(EnrollmentTransaction $transaction): RedirectResponse
    {
        Gate::authorize('delete', $transaction);

        try {
            DB::transaction(fn () => $transaction->delete());
        } catch (InvalidUnitMovementException $e) {
            return redirect()->route('transactions.show', $transaction)->with('error', $e->getMessage());
        }

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction deleted and the unit change was reversed.');
    }
}
