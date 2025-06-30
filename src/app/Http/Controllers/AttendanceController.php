<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function showAttendanceForm()
    {
        $user = Auth::user();
        $today = now()->format('Y-m-d');

        $attendance = Attendance::with('workBreaks')
            ->where('user_id', $user->id)
            ->where('work_date', $today)
            ->first();

        $status = $this->getAttendanceStatus($attendance);

        $latestBreak = $attendance && $attendance->workBreaks->count() > 0 ? $attendance->workBreaks->sortByDesc('break_start')->first() : null;

        return view('attendance.form', compact('attendance', 'status', 'latestBreak'));
    }

    private function getAttendanceStatus($attendance)
    {
        if (!$attendance || !$attendance->clock_in) {
            return '勤務外';
        } elseif ($attendance->clock_in && !$attendance->clock_out) {
            $lastBreak = $attendance->workBreaks()->orderBy('id', 'desc')->first();
            if ($lastBreak && $lastBreak->break_end === null) {
                return '休憩中';
            }
            return '出勤中';
        } elseif ($attendance->clock_out) {
            return '退勤済';
        }
        return '勤務外';
    }

    public function clockIn()
    {
        $user = Auth::user();
        $today = now()->format('Y-m-d');

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->first();

        if ($attendance && $attendance->clock_in) {
            return redirect()->route('attendance.form')
                ->withErrors(['すでに本日の出勤が登録されています。']);
        }

        if (!$attendance) {
            $attendance = new Attendance();
            $attendance->user_id = $user->id;
            $attendance->work_date = $today;
        }
        $attendance->clock_in = now()->format('H:i');
        $attendance->save();

        return redirect()->route('attendance.form');
    }

    public function clockOut()
    {
        $user = Auth::user();
        $today = now()->format('Y-m-d');

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->first();

        if (!$attendance || !$attendance->clock_in) {
            return redirect()->route('attendance.form')
                ->withErrors(['本日の出勤記録がありません。']);
        }
        if ($attendance->clock_out) {
            return redirect()->route('attendance.form')
                ->withErrors(['すでに本日の退勤が登録されています。']);
        }

        $attendance->clock_out = now()->format('H:i');
        $attendance->save();

        return redirect()->route('attendance.form');
    }

    public function breakStart()
    {
        $user = Auth::user();
        $today = now()->format('Y-m-d');

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->first();

        if (!$attendance || !$attendance->clock_in) {
            return redirect()->route('attendance.form')
                ->withErrors(['本日の出勤記録がありません。']);
        }

        $latestBreak = $attendance->workBreaks()->latest()->first();
        if ($latestBreak && !$latestBreak->break_end) {
            return redirect()->route('attendance.form')
                ->withErrors(['すでに休憩中です。']);
        }

        $attendance->workBreaks()->create([
            'break_start' => now()->format('H:i')
        ]);

        return redirect()->route('attendance.form');
    }

    public function breakEnd()
    {
        $user = Auth::user();
        $today = now()->format('Y-m-d');

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->first();

        if (!$attendance || !$attendance->clock_in) {
            return redirect()->route('attendance.form')
                ->withErrors(['本日の出勤記録がありません。']);
        }

        $latestBreak = $attendance->workBreaks()->latest()->first();

        if (!$latestBreak || $latestBreak->break_end) {
            return redirect()->route('attendance.form')
                ->withErrors(['休憩中ではありません。']);
        }

        $latestBreak->break_end = now()->format('H:i');
        $latestBreak->save();

        return redirect()->route('attendance.form');
    }

    public function listMyAttendances(Request $request)
    {
        $user = Auth::user();

        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $startDate = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $attendances = Attendance::with('workBreaks')
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [$startDate, $endDate])
            ->get()
            ->keyBy('work_date');

        $daysInMonth = [];
        $attendanceSummary = [];

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $workDate = $date->format('Y-m-d');
            $attendance = $attendances->get($workDate);

            $hasBreak = false;
            $breakMinutes = 0;
            if ($attendance && $attendance->workBreaks->count()) {
                $hasBreak = true;
                foreach ($attendance->workBreaks as $break) {
                    if ($break->break_start && $break->break_end) {
                        $breakMinutes += \Carbon\Carbon::parse($break->break_end)
                            ->diffInMinutes(\Carbon\Carbon::parse($break->break_start));
                    }
                }
            }

            $workMinutes = null;
            if ($attendance && $attendance->clock_in && $attendance->clock_out) {
                $workMinutes = \Carbon\Carbon::parse($attendance->clock_out)
                    ->diffInMinutes(\Carbon\Carbon::parse($attendance->clock_in)) - $breakMinutes;
            }

            $attendanceSummary[$workDate] = [
                'attendance' => $attendance,
                'breakMinutes' => $breakMinutes,
                'workMinutes' => $workMinutes,
                'hasBreak' => $hasBreak,
            ];

            $daysInMonth[] = $date->copy();
        }

        return view('attendance.list', compact('attendanceSummary', 'daysInMonth', 'year', 'month'));
    }

    public function showAttendanceDetail($id, Request $request)
    {
        $user = Auth::user();
        $correctionRequestId = $request->input('correction_request_id');

        if ($user->is_admin) {
            $attendance = Attendance::with(['user', 'workBreaks', 'correctionRequests'])->findOrFail($id);
            if ($correctionRequestId) {
                $correctionRequest = $attendance->correctionRequests()->with('correctionBreaks')->findOrFail($correctionRequestId);
            } else {
                $correctionRequest = $attendance->correctionRequests()->orderByDesc('created_at')->first();
            }
            $breaks = $correctionRequest ? $correctionRequest->correctionBreaks : collect();
            $isPending = $correctionRequest && $correctionRequest->approval_status === 'pending';
            $isAdmin = true;
            return view('attendance.detail', compact('attendance', 'isPending', 'correctionRequest', 'breaks', 'isAdmin'));
        } else {
            $attendance = Attendance::with(['workBreaks', 'correctionRequests'])
                ->where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();
            if ($correctionRequestId) {
                $correctionRequest = $attendance->correctionRequests()->with('correctionBreaks')->findOrFail($correctionRequestId);
            } else {
                $correctionRequest = $attendance->correctionRequests()
                    ->with('correctionBreaks')
                    ->where('user_id', $user->id)
                    ->orderByDesc('created_at')
                    ->first();
            }
            $breaks = $correctionRequest ? $correctionRequest->correctionBreaks : collect();
            $isPending = $correctionRequest && $correctionRequest->approval_status === 'pending';
            $isAdmin = false;
            return view('attendance.detail', compact('attendance', 'breaks', 'correctionRequest', 'isPending', 'isAdmin'));
        }
    }
}
