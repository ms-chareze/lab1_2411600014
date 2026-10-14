<?php

namespace App\Http\Controllers;

use App\Models\Student;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with real statistics and the Lab 4 charts.
     */
    public function index()
    {
        // ---- Metric cards -------------------------------------------------
        $totalStudents  = Student::count();
        $averageGpa     = (float) Student::avg('gpa');
        $warningCount   = Student::whereIn('status', Student::WARNING_STATUSES)->count();
        $totalUnits     = (int) Student::sum('enrolled_units');

        // ---- Academic warning alert ---------------------------------------
        $warningStudents = Student::whereIn('status', Student::WARNING_STATUSES)
            ->orderByDesc('gpa')
            ->get();

        // ---- Recently added table -----------------------------------------
        $recentStudents = Student::latest()->take(5)->get();

        // ---- Chart 1: Course Distribution (bar) ---------------------------
        $courseCounts = Student::selectRaw('course, COUNT(*) as total')
            ->groupBy('course')
            ->orderBy('course')
            ->pluck('total', 'course');

        // ---- Chart 2: Academic Status Ratio (doughnut) --------------------
        $dbStatusCounts = Student::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusCounts = [];
        foreach (array_keys(Student::STATUSES) as $status) {
            $statusCounts[$status] = (int) ($dbStatusCounts[$status] ?? 0);
        }

        // ---- Chart 3: Top Performing GPA (horizontal bar) -----------------
        // This portal uses the 1.00 (highest) to 5.00 (lowest) grading scale,
        // so the best students are the ones with the LOWEST GPA value.
        $topStudents = Student::orderBy('gpa')->orderBy('name')->take(5)->get(['name', 'gpa']);

        $chartData = [
            'course' => [
                'labels' => $courseCounts->keys()->values(),
                'data'   => $courseCounts->values()->map(fn ($v) => (int) $v),
            ],
            'status' => [
                'labels' => array_keys($statusCounts),
                'data'   => array_values($statusCounts),
                'colors' => ['#28a745', '#17a2b8', '#ffc107', '#dc3545'],
            ],
            'top' => [
                'labels' => $topStudents->pluck('name')->values(),
                'data'   => $topStudents->pluck('gpa')->map(fn ($v) => (float) $v)->values(),
            ],
        ];

        return view('dashboard', compact(
            'totalStudents',
            'averageGpa',
            'warningCount',
            'totalUnits',
            'warningStudents',
            'recentStudents',
            'chartData'
        ));
    }
}
