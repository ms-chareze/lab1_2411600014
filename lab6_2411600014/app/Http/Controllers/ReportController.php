<?php

namespace App\Http\Controllers;

use App\Models\EnrollmentTransaction;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Lab 6 Part 9: server-side reports with CSV export (?export=csv). */
class ReportController extends Controller
{
    /** Stock Movement Report: transactions inside a date range. */
    public function movement(Request $request): View|StreamedResponse
    {
        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to'   => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $from = $filters['date_from'] ?? now()->subDays(30)->toDateString();
        $to = $filters['date_to'] ?? now()->toDateString();

        $transactions = EnrollmentTransaction::with(['student', 'user'])
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->recent()
            ->get();

        $totalAdded = $transactions->where('quantity', '>', 0)->sum('quantity');
        $totalDropped = abs($transactions->where('quantity', '<', 0)->sum('quantity'));

        if ($request->query('export') === 'csv') {
            return $this->csv(
                "stock-movement-{$from}-to-{$to}.csv",
                ['Date/Time', 'Student ID', 'Student', 'Type', 'Quantity', 'Reference', 'Reason', 'Staff', 'Notes'],
                $transactions->map(fn ($t) => [
                    $t->created_at->format('Y-m-d H:i:s'),
                    $t->student?->student_id,
                    $t->student?->name,
                    $t->type_label,
                    $t->quantity,
                    $t->reference_number,
                    $t->reason,
                    $t->user?->name,
                    $t->notes,
                ])
            );
        }

        return view('reports.movement', compact('transactions', 'from', 'to', 'totalAdded', 'totalDropped'));
    }

    /** Low Stock Report: students at or below their minimum load. */
    public function lowLoad(Request $request): View|StreamedResponse
    {
        $students = Student::lowLoad()->orderBy('enrolled_units')->orderBy('name')->get();

        if ($request->query('export') === 'csv') {
            return $this->csv(
                'low-load-report-' . now()->format('Y-m-d') . '.csv',
                ['Student ID', 'Name', 'Course', 'Enrolled Units', 'Minimum Units', 'Units To Add', 'Status'],
                $students->map(fn ($s) => [
                    $s->student_id, $s->name, $s->course, $s->enrolled_units, $s->min_units,
                    $s->min_units - $s->enrolled_units + 1, $s->load_status,
                ])
            );
        }

        return view('reports.low-load', compact('students'));
    }

    /** Inventory Valuation Report: units x tuition rate per unit. */
    public function valuation(Request $request): View|StreamedResponse
    {
        $data = $request->validate(['rate' => ['nullable', 'numeric', 'min:0', 'max:1000000']]);
        $rate = (float) ($data['rate'] ?? 1000);

        $students = Student::orderBy('course')->orderBy('name')->get();

        $byCourse = $students->groupBy('course')->map(fn ($group) => [
            'students' => $group->count(),
            'units'    => $group->sum('enrolled_units'),
            'value'    => $group->sum('enrolled_units') * $rate,
        ]);

        $totalUnits = $students->sum('enrolled_units');
        $totalValue = $totalUnits * $rate;

        if ($request->query('export') === 'csv') {
            return $this->csv(
                'valuation-report-' . now()->format('Y-m-d') . '.csv',
                ['Student ID', 'Name', 'Course', 'Enrolled Units', 'Rate Per Unit', 'Assessed Value'],
                $students->map(fn ($s) => [
                    $s->student_id, $s->name, $s->course, $s->enrolled_units,
                    number_format($rate, 2, '.', ''), number_format($s->enrolled_units * $rate, 2, '.', ''),
                ])
            );
        }

        return view('reports.valuation', compact('students', 'byCourse', 'rate', 'totalUnits', 'totalValue'));
    }

    private function csv(string $filename, array $header, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($header, $rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $header);
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
