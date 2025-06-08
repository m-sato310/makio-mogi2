<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AdminAttendanceController extends Controller
{
    public function listAllAttendances(Request $request)
    {
        $targetDate = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::today();

        $attendances = Attendance::with('workBreaks', 'user')->whereDate('work_date', $targetDate->format('Y-m-d'))->get()->keyBy('user_id');
        $users = User::where('is_admin' , false)->orderBy('name')->get();

        $attendanceSummary = [];
        foreach ($users as $user) {
            $attendance = $attendances->get($user->id);

            $breakMinutes = 0;
            if ($attendance && $attendance->workBreaks->count()) {
                foreach ($attendance->workBreaks as $break) {
                    if ($break->break_start && $break->break_end) {
                        $breakMinutes += Carbon::parse($break->break_end)->diffInMinutes(Carbon::parse($break->break_start));
                    }
                }
            }

            $workMinutes = null;
            if ($attendance && $attendance->clock_in && $attendance->clock_out) {
                $workMinutes = Carbon::parse($attendance->clock_out)->diffInMinutes(Carbon::parse($attendance->clock_in)) - $breakMinutes;
            }

            $attendanceSummary[$user->id] = [
                'user' => $user,
                'attendance' => $attendance,
                'breakMinutes' => $breakMinutes,
                'workMinutes' => $workMinutes,
            ];
        }

        $isAdmin = true;

        return view('attendance.list', compact(
            'attendanceSummary',
            'targetDate',
            'isAdmin'
        ));
    }

    public function showStaffAttendanceDetail($id)
    {
        $attendance = Attendance::with(['user', 'workBreaks'])->findOrFail($id);

        $isPending = false;

        return view('attendance.detail', compact('attendance', 'isPending'))->with('isAdmin', true);
    }
}
