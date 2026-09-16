<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with computed statistics.
     */
    public function index()
    {
        // Calculate statistics for the student grade portal
        $totalStudents = Student::count();
        $averageGpa = Student::avg('gpa');
        $regularCount = Student::where('status', 'Regular')->count();
        $irregularCount = Student::where('status', 'Irregular')->count();
        $totalUnits = Student::sum('enrolled_units');

        return view('dashboard', compact(
            'totalStudents',
            'averageGpa',
            'regularCount',
            'irregularCount',
            'totalUnits'
        ));
    }
}