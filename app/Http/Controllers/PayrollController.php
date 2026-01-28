<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OvertimeSubmission;
use App\Models\Payroll;
use App\Models\Bonus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayrollController extends Controller
{
    /**
     * KARYAWAN: Mengajukan Lembur
     */
    public function submitOvertime(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'duration' => 'required|integer|min:1', // Jam
        ]);

        $overtime = OvertimeSubmission::create([
            'user_id' => Auth::id(),
            'date' => $request->date,
            'duration' => $request->duration,
            'status' => 'pending'
        ]);

        return response()->json(['success' => true, 'data' => $overtime]);
    }

    /**
     * ADMIN: Approve Lembur
     */
    public function approveOvertime(Request $request, $id)
    {
        $overtime = OvertimeSubmission::findOrFail($id);
        $overtime->update(['status' => $request->status]); // approved / rejected

        return response()->json(['success' => true, 'message' => 'Status lembur diperbarui']);
    }

    /**
     * ADMIN: Generate Gaji Bulanan
     */
    public function generatePayroll(Request $request)
    {
        $request->validate([
            'month' => 'required|integer',
            'year' => 'required|integer',
            'user_id' => 'required|exists:users,id'
        ]);

        $user = User::with('profile.position')->findOrFail($request->user_id);
        $position = $user->profile->position;

        if (!$position) {
            return response()->json(['message' => 'User tidak memiliki jabatan/gaji pokok'], 400);
        }

        // 1. Hitung Lembur yang sudah Approved
        $totalOvertimeHours = OvertimeSubmission::where('user_id', $user->id)
            ->whereMonth('date', $request->month)
            ->whereYear('date', $request->year)
            ->where('status', 'approved')
            ->sum('duration');

        $overtimePay = $totalOvertimeHours * $position->overtime_rate;

        // 2. Ambil Bonus
        $bonusPay = Bonus::where('user_id', $user->id)
            ->whereMonth('date', $request->month)
            ->whereYear('date', $request->year)
            ->sum('amount');

        // 3. Hitung Gaji Bersih
        $basicSalary = $position->base_salary;
        $netSalary = $basicSalary + $overtimePay + $bonusPay;

        // 4. Simpan ke Tabel Payrolls
        $payroll = Payroll::updateOrCreate(
            [
                'user_id' => $user->id,
                'month' => $request->month,
                'year' => $request->year,
            ],
            [
                'basic_salary' => $basicSalary,
                'overtime_pay' => $overtimePay,
                'bonus_pay' => $bonusPay,
                'deductions' => 0, // Bisa ditambah logika potongan jika terlambat
                'net_salary' => $netSalary,
                'total_attendance' => 0 // Opsional: hitung dari tabel attendance
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Payroll berhasil di-generate',
            'data' => $payroll
        ]);
    }
}