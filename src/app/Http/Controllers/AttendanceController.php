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
            $lastBreak = $attendance->workBreaks->sortByDesc('break_start')->first();
            if ($lastBreak && !$lastBreak->break_end) {
                return '休憩中';
            }
            return '出勤中';
        } elseif ($attendance->clock_out) {
            return '退勤済';
        }
        return '勤務外';
    }

    public function clockIn(Request $request)
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
        $attendance->clock_in = now()->format('H:i:s');
        $attendance->save();

        return redirect()->route('attendance.form');
    }

    public function clockOut(Request $request)
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

        $attendance->clock_out = now()->format('H:i:s');
        $attendance->save();

        return redirect()->route('attendance.form');
    }

    public function breakStart(Request $request)
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
            'break_start' => now()->format('H:i:s')
        ]);

        return redirect()->route('attendance.form');
    }

    public function breakEnd(Request $request)
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

        $latestBreak->break_end = now()->format('H:i:s');
        $latestBreak->save();

        return redirect()->route('attendance.form');
    }
}
