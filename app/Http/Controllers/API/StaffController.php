<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    // ✅ 1. Staff List API
    public function index(Request $request)
    {
        $query = DB::table('users')
            ->where('role', 'staff')
            ->select('id', 'name', 'email', 'contact', 'created_at');

        // 🔍 Search (optional)
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('contact', 'like', '%' . $request->search . '%');
            });
        }

        $staff = $query->orderBy('id', 'desc')->get();

        return response()->json([
            'success' => true,
            'total_staff' => $staff->count(),
            'data' => $staff
        ]);
    }

    // ✅ 2. Add Staff
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'nullable|email|unique:users,email',
            'contact' => 'required',
            'password' => 'required|min:4'
        ]);

        $id = DB::table('users')->insertGetId([
            'name' => $request->name,
            'email' => $request->email,
            'contact' => $request->contact,
            'password' => Hash::make($request->password),
            'role' => 'staff',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Staff added successfully',
            'id' => $id
        ]);
    }

    // ✅ 3. Update Staff
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'contact' => 'required',
        ]);

        $data = [
            'name' => $request->name,
            'contact' => $request->contact,
            'updated_at' => now(),
        ];

        if ($request->password) {
            $data['password'] = Hash::make($request->password);
        }

        DB::table('users')
            ->where('id', $id)
            ->where('role', 'staff')
            ->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Staff updated successfully'
        ]);
    }

    // ✅ 4. Delete Staff
    public function destroy($id)
    {
        DB::table('users')
            ->where('id', $id)
            ->where('role', 'staff')
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Staff deleted successfully'
        ]);
    }

    // ✅ 5. Single Staff
    public function show($id)
    {
        $staff = DB::table('users')
            ->where('id', $id)
            ->where('role', 'staff')
            ->first();

        if (!$staff) {
            return response()->json([
                'success' => false,
                'message' => 'Staff not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $staff
        ]);
    }
}