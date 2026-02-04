<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use App\Exports\AttendanceReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
class ReportController extends Controller
{
    public function dashboardStats()
    {
        $today = Carbon::today()->format('Y-m-d');
        $totalEmployees = User::where('role', 'employee')->count();

        $hadir = Attendance::whereDate('date', $today)->where('status', 'hadir')->count();
        $terlambat = Attendance::whereDate('date', $today)->where('status', 'terlambat')->count();
        $izin = Attendance::whereDate('date', $today)->where('status', 'izin')->count();

        // Menghitung yang belum absen sama sekali
        $alpha = $totalEmployees - ($hadir + $terlambat + $izin);

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_employees' => $totalEmployees,
                    'hadir' => $hadir,
                    'terlambat' => $terlambat,
                    'izin' => $izin,
                    'alpha' => max(0, $alpha),
                ],
                'today_details' => Attendance::with('user.profile')
                    ->whereDate('date', $today)
                    ->get()
            ]
        ]);
    }
    
}