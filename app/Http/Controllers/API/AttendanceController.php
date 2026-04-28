<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\StaffAttendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    // ✅ Mark Attendance
    public function markAttendance(Request $request)
    {
        try {
            \Log::info('Mark Attendance', $request->all());

            $request->validate([
                'staff_id' => 'required|exists:users,id',
                'date' => 'required|date',
                'status' => 'required|in:present,absent',
            ]);

            $attendance = StaffAttendance::updateOrCreate(
                [
                    'staff_id' => $request->staff_id,
                    'date' => $request->date,
                ],
                [
                    'status' => $request->status
                ]
            );

            return response()->json([
                'status' => true,
                'message' => 'Attendance marked successfully',
                'data' => $attendance
            ]);
        } catch (\Exception $e) {
            \Log::error('Attendance Error', ['error' => $e->getMessage()]);

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong'
            ], 500);
        }
    }

    // ✅ Attendance List
    // public function attendanceList(Request $request)
    // {
    //     try {
    //         \Log::info('Attendance List', $request->all());

    //         $query = StaffAttendance::with('staff:id,name,contact');

    //         if ($request->staff_id) {
    //             $query->where('staff_id', $request->staff_id);
    //         }

    //         if ($request->date) {
    //             $query->whereDate('date', $request->date);
    //         }

    //         if ($request->from_date && $request->to_date) {
    //             $query->whereBetween('date', [
    //                 $request->from_date,
    //                 $request->to_date
    //             ]);
    //         }

    //         $attendance = $query->orderBy('date', 'desc')->paginate(20);

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Attendance list fetched',
    //             'data' => $attendance
    //         ]);
    //     } catch (\Exception $e) {
    //         \Log::error('Attendance List Error', ['error' => $e->getMessage()]);

    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Something went wrong'
    //         ], 500);
    //     }
    // }

    // public function attendanceList(Request $request)
    // {
    //     try {
    //         \Log::info('Attendance List', $request->all());

    //         $query = StaffAttendance::with('staff:id,name,contact');

    //         // filters
    //         if ($request->staff_id) {
    //             $query->where('staff_id', $request->staff_id);
    //         }

    //         if ($request->from_date && $request->to_date) {
    //             $query->whereBetween('date', [
    //                 $request->from_date,
    //                 $request->to_date
    //             ]);
    //         }

    //         // ❌ pagination हटाया
    //         $attendance = $query->orderBy('date', 'desc')->get();

    //         // 🔥 SUMMARY CALCULATION
    //         $summary = StaffAttendance::select(
    //             'staff_id',
    //             \DB::raw("SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as total_present"),
    //             \DB::raw("SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as total_absent")
    //         )
    //             ->groupBy('staff_id')
    //             ->with('staff:id,name,contact')
    //             ->get()
    //             ->map(function ($item) {
    //                 return [
    //                     'staff_id' => $item->staff_id,
    //                     'name' => $item->staff->name ?? '',
    //                     'contact' => $item->staff->contact ?? '',
    //                     'total_present' => (int) $item->total_present,
    //                     'total_absent' => (int) $item->total_absent,
    //                 ];
    //             });

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Attendance list fetched',
    //             'data' => [
    //                 'summary' => $summary,
    //                 'attendance' => $attendance
    //             ]
    //         ]);
    //     } catch (\Exception $e) {
    //         \Log::error('Attendance List Error', ['error' => $e->getMessage()]);

    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Something went wrong'
    //         ], 500);
    //     }
    // }

    public function attendanceList(Request $request)
    {
        try {
            \Log::info('Attendance List', $request->all());

            // =========================
            // 📅 Date Logic (Flexible)
            // =========================
            $fromDate = null;
            $toDate = null;

            // 1. Month filter
            if ($request->month) {
                $fromDate = \Carbon\Carbon::parse($request->month)->startOfMonth()->format('Y-m-d');
                $toDate = \Carbon\Carbon::parse($request->month)->endOfMonth()->format('Y-m-d');
            }
            // 2. Single date
            elseif ($request->date) {
                $fromDate = $request->date;
                $toDate = $request->date;
            }
            // 3. Custom range
            elseif ($request->from_date && $request->to_date) {
                $fromDate = $request->from_date;
                $toDate = $request->to_date;
            }

            // 4. Default → all data (no filter)

            // =========================
            // 📌 Attendance Query
            // =========================
            $attendanceQuery = StaffAttendance::with('staff:id,name,contact');

            if ($fromDate && $toDate) {
                $attendanceQuery->whereBetween('date', [$fromDate, $toDate]);
            }

            if ($request->staff_id) {
                $attendanceQuery->where('staff_id', $request->staff_id);
            }

            $attendance = $attendanceQuery
                ->orderBy('date', 'desc')
                ->get();

            // =========================
            // 📊 Summary Query
            // =========================
            $summaryQuery = StaffAttendance::select(
                'staff_id',
                \DB::raw("SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as total_present"),
                \DB::raw("SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as total_absent")
            );

            if ($fromDate && $toDate) {
                $summaryQuery->whereBetween('date', [$fromDate, $toDate]);
            }

            if ($request->staff_id) {
                $summaryQuery->where('staff_id', $request->staff_id);
            }

            $summary = $summaryQuery
                ->groupBy('staff_id')
                ->with('staff:id,name,contact')
                ->get()
                ->map(function ($item) {
                    $total = $item->total_present + $item->total_absent;

                    return [
                        'staff_id' => $item->staff_id,
                        'name' => $item->staff->name ?? '',
                        'contact' => $item->staff->contact ?? '',
                        'total_present' => (int) $item->total_present,
                        'total_absent' => (int) $item->total_absent,
                        'total_days' => (int) $total,
                        'attendance_percentage' => $total > 0
                            ? round(($item->total_present / $total) * 100, 2)
                            : 0
                    ];
                });

            // =========================
            // 📦 Final Response
            // =========================
            return response()->json([
                'status' => true,
                'message' => 'Attendance fetched successfully',
                'filters' => [
                    'staff_id' => $request->staff_id ?? null,
                    'date' => $request->date ?? null,
                    'month' => $request->month ?? null,
                    'from_date' => $fromDate,
                    'to_date' => $toDate,
                ],
                'data' => [
                    'summary' => $summary,
                    'attendance' => $attendance
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Attendance List Error', ['error' => $e->getMessage()]);

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong'
            ], 500);
        }
    }
}
