<?php

namespace App\Http\Controllers;

use App\Models\StaffAttendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    // List Staff
    // public function index()
    // {
    //     $staff = User::where('role', 'staff')->latest()->get();
    //     return view('admin.staff.index', compact('staff'));
    // }

    public function index(Request $request)
    {
        $query = User::where('role', 'staff');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q
                    ->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->date) {
            $query->whereDate('created_at', $request->date);
        }

        $staff = $query->latest()->paginate(10);

        return view('admin.staff.index', compact('staff'));
    }

    // Add Page
    public function create()
    {
        return view('admin.staff.create');
    }

    // Store Staff

    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'name' => 'required|string|max:255',
    //         'email' => 'required|email|unique:users,email',
    //         'password' => 'required|min:6|confirmed'
    //     ]);

    //     User::create([
    //         'name' => $validated['name'],
    //         'email' => $validated['email'],
    //         'role' => 'staff',
    //         'password' => Hash::make($validated['password']),
    //     ]);

    //     return redirect()
    //         ->route('staff')
    //         ->with('success', 'Staff Added Successfully');
    // }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'contact' => 'required|digits_between:8,15',
            'password' => 'required|min:6|confirmed'
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'contact' => $validated['contact'],
            'role' => 'staff',
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()
            ->route('staff')
            ->with('success', 'Staff Added Successfully');
    }

    // View Staff
    public function show($id)
    {
        $staff = User::findOrFail($id);
        return view('admin.staff.view', compact('staff'));
    }

    // Edit Page
    public function edit($id)
    {
        $staff = User::findOrFail($id);

        return view('admin.staff.edit', compact('staff'));
    }

    // Update Staff

    public function update(Request $request, $id)
    {
        $staff = User::findOrFail($id);

        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
            'contact' => 'required|digits_between:8,15'
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'contact' => $request->contact
        ];

        if ($request->password) {
            $data['password'] = Hash::make($request->password);
        }

        $staff->update($data);

        return redirect()->route('staff')->with('success', 'Staff Updated Successfully');
    }

    public function attendance(Request $request)
    {
        $date = $request->date ?? date('Y-m-d');

        $staff = User::where('role', 'staff')->get();

        $attendance = StaffAttendance::where('date', $date)
            ->pluck('status', 'staff_id');

        return view('admin.staff.attendance', compact('staff', 'attendance', 'date'));
    }

    public function saveAttendance(Request $request)
    {
        foreach ($request->attendance as $staff_id => $status) {
            StaffAttendance::updateOrCreate(
                [
                    'staff_id' => $staff_id,
                    'date' => $request->date
                ],
                [
                    'status' => $status
                ]
            );
        }

        return back()->with('success', 'Attendance Saved');
    }

    public function monthlyReport(Request $request)
    {
        $month = $request->month ?? date('Y-m');

        $start = \Carbon\Carbon::parse($month . '-01');
        $end = $start->copy()->endOfMonth();

        $staff = User::where('role', 'staff')->get();

        $attendance = StaffAttendance::whereBetween('date', [$start, $end])
            ->get()
            ->groupBy(['staff_id', function ($item) {
                return date('d', strtotime($item->date));
            }]);

        return view('admin.staff.monthly_report', compact('staff', 'attendance', 'start', 'end', 'month'));
    }
}
