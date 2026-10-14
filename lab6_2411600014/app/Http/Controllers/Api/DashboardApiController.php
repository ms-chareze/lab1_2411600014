<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EnrollmentTransactionResource;
use App\Http\Resources\StudentResource;
use App\Models\EnrollmentTransaction;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * JSON endpoints polled by the dashboard every 30 seconds.
 * Consistent shape: { "data": ..., "meta": ... } (API Resources).
 */
class DashboardApiController extends Controller
{
    /** GET /api/dashboard/stats */
    public function stats(Request $request): JsonResponse
    {
        return response()->json([
            'data' => [
                'total_students'       => Student::count(),
                'average_gpa'          => round((float) Student::avg('gpa'), 2),
                'warning_count'        => Student::whereIn('status', Student::WARNING_STATUSES)->count(),
                'total_units'          => (int) Student::sum('enrolled_units'),
                'low_load_count'       => Student::lowLoad()->count(),
                'no_load_count'        => Student::noLoad()->count(),
                'transactions_today'   => EnrollmentTransaction::whereDate('created_at', today())->count(),
                'unread_notifications' => $request->user()->unreadNotifications()->count(),
            ],
            'meta' => [
                'generated_at'       => now()->toIso8601String(),
                'generated_at_human' => now()->format('M d, Y h:i:s A'),
            ],
        ]);
    }

    /** GET /api/products/low-stock  (here: /api/students/low-load) */
    public function lowLoad()
    {
        $students = Student::lowLoad()->orderBy('enrolled_units')->orderBy('name')->get();

        return StudentResource::collection($students)->additional([
            'meta' => ['count' => $students->count()],
        ]);
    }

    /** GET /api/transactions/recent */
    public function recentTransactions()
    {
        $transactions = EnrollmentTransaction::with(['student', 'user'])->recent()->limit(8)->get();

        return EnrollmentTransactionResource::collection($transactions);
    }

    /** GET /api/students/{student}/units  (used by the adjustment form) */
    public function studentUnits(Student $student): StudentResource
    {
        return new StudentResource($student);
    }
}
