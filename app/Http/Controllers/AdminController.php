<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Office;
use App\Models\Position;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AdminController extends Controller
{

    public function index()
    {
        $today = Carbon::today()->format('Y-m-d');

        $stats = [
            'total' => User::where('role', 'employee')->count(),
            'hadir' => Attendance::whereDate('date', $today)->where('status', 'hadir')->count(),
            'terlambat' => Attendance::whereDate('date', $today)->where('status', 'terlambat')->count(),
            'izin' => Attendance::whereDate('date', $today)->where('status', 'izin')->count(),
        ];

        $recentAttendances = Attendance::with('user.profile')
            ->whereDate('date', $today)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.dashboard', compact('stats', 'recentAttendances'));
    }
    // Kelola Kantor (Geofencing)
    public function getOffices()
    {
        return response()->json(['data' => Office::all()]);
    }

    public function updateOffice(Request $request, $id)
    {
        $office = Office::findOrFail($id);
        $office->update($request->all());
        return response()->json(['message' => 'Lokasi kantor diperbarui']);
    }

    // Kelola Jabatan (Gaji)
    public function getPositions()
    {
        return response()->json(['data' => Position::all()]);
    }
}