<?php

namespace App\Http\Controllers;

use App\Http\Requests\CorrectionRequestRequest;
use App\Models\Attendance;
use App\Models\CorrectionBreak;
use App\Models\CorrectionRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminAttendanceController extends Controller
{
    public function listAllAttendances(Request $request)
    {
        $targetDate = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::today();

        $attendances = Attendance::with('workBreaks', 'user')->whereDate('work_date', $targetDate->format('Y-m-d'))->get()->keyBy('user_id');
        $users = User::where('is_admin', false)->orderBy('name')->get();

        $attendanceSummary = [];
        foreach ($users as $user) {
            $attendance = $attendances->get($user->id);

            $breakMinutes = 0;
            $hasBreak = false;
            if ($attendance && $attendance->workBreaks->count()) {
                $hasBreak = true;
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
                'hasBreak' => $hasBreak,
            ];
        }

        $isAdmin = true;

        return view('attendance.list', compact(
            'attendanceSummary',
            'targetDate',
            'isAdmin'
        ));
    }

    // public function showStaffAttendanceDetail($id)
    // {
    //     $attendance = Attendance::with(['user', 'workBreaks', 'correctionRequests'])->findOrFail($id);

    //     $isPending = false;

    //     $correctionRequest = $attendance->correctionRequests()->orderByDesc('created_at')->first();

    //     $breaks = $attendance->workBreaks;

    //     return view('attendance.detail', compact('attendance', 'isPending', 'correctionRequest', 'breaks'))->with('isAdmin', true);
    // }

    public function updateStaffAttendance(CorrectionRequestRequest $request, $id)
    {
        DB::transaction(function () use ($request, $id) {
            $attendance = Attendance::with(['user', 'workBreaks'])->findOrFail($id);

            $correctionRequest = CorrectionRequest::create([
                'attendance_id' => $attendance->id,
                'user_id' => $attendance->user_id,
                'new_clock_in' => $request->input('new_clock_in'),
                'new_clock_out' => $request->input('new_clock_out'),
                'remarks' => $request->input('remarks'),
                'approval_status' => 'approved',
                'approved_at' => now(),
            ]);

            foreach ($request->input('new_breaks', []) as $break) {
                if (!empty($break['new_break_start']) && !empty($break['new_break_end'])) {
                    CorrectionBreak::create([
                        'correction_request_id' => $correctionRequest->id,
                        'new_break_start' => $break['new_break_start'],
                        'new_break_end' => $break['new_break_end'],
                    ]);
                }
            }

            $attendance->workBreaks()->delete();
            foreach ($request->input('new_breaks', []) as $break) {
                if (!empty($break['new_break_start']) && !empty($break['new_break_end'])) {
                    $attendance->workBreaks()->create([
                        'break_start' => $break['new_break_start'],
                        'break_end' => $break['new_break_end'],
                    ]);
                }
            }

            $attendance->update([
                'clock_in' => $request->input('new_clock_in'),
                'clock_out' => $request->input('new_clock_out'),
            ]);

            $correctionRequest->update([
                'remarks' => $request->input('remarks'),
            ]);
        });

        return redirect()->route('attendance.detail', ['id' => $id]);
    }

    public function listStaffAttendances(Request $request, $id)
    {
        $staff = User::findOrFail($id);

        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $attendances = Attendance::with('workBreaks')
            ->where('user_id', $staff->id)
            ->whereBetween('work_date', [$startDate, $endDate])
            ->get()
            ->keyBy('work_date');

        $daysInMonth = [];
        $attendanceSummary = [];

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $workDate = $date->format('Y-m-d');
            $attendance = $attendances->get($workDate);

            $breakMinutes = 0;
            if ($attendance && $attendance->workBreaks->count()) {
                foreach ($attendance->workBreaks as $break) {
                    if ($break->break_start && $break->break_end) {
                        $breakMinutes += Carbon::parse($break->break_end)
                            ->diffInMinutes(Carbon::parse($break->break_start));
                    }
                }
            }

            $workMinutes = null;
            if ($attendance && $attendance->clock_in && $attendance->clock_out) {
                $workMinutes = Carbon::parse($attendance->clock_out)
                    ->diffInMinutes(Carbon::parse($attendance->clock_in)) - $breakMinutes;
            }

            $attendanceSummary[$workDate] = [
                'attendance' => $attendance,
                'breakMinutes' => $breakMinutes,
                'workMinutes' => $workMinutes,
            ];

            $daysInMonth[] = $date->copy();
        }

        return view('admin.attendance.staff_attendance_list', compact(
            'staff',
            'attendanceSummary',
            'daysInMonth',
            'year',
            'month'
        ));
    }

    public function exportStaffAttendanceCsv(Request $request, $id)
    {
        $staff = User::findOrFail($id);
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $attendances = Attendance::with('workBreaks')
            ->where('user_id', $staff->id)
            ->whereBetween('work_date', [$startDate, $endDate])
            ->orderBy('work_date')
            ->get()
            ->keyBy('work_date');

        $days = [];
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $workDate = $date->format('Y-m-d');
            $attendance = $attendances->get($workDate);

            $breakMinutes = 0;
            if ($attendance && $attendance->workBreaks->count()) {
                foreach ($attendance->workBreaks as $break) {
                    if ($break->break_start && $break->break_end) {
                        $breakMinutes += Carbon::parse($break->break_end)
                            ->diffInMinutes(Carbon::parse($break->break_start));
                    }
                }
            }

            $workMinutes = null;
            if ($attendance && $attendance->clock_in && $attendance->clock_out) {
                $workMinutes = Carbon::parse($attendance->clock_out)
                    ->diffInMinutes(Carbon::parse($attendance->clock_in)) - $breakMinutes;
            }

            $days[] = [
                '日付' => $date->format('Y-m-d'),
                '出勤' => $attendance && $attendance->clock_in ? Carbon::parse($attendance->clock_in)->format('H:i') : '',
                '退勤' => $attendance && $attendance->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : '',
                '休憩' => $breakMinutes ? floor($breakMinutes/60) . ':' . str_pad($breakMinutes%60, 2, '0', STR_PAD_LEFT) : '',
                '合計' => !is_null($workMinutes) ? floor($workMinutes/60) . ':' . str_pad($workMinutes%60, 2, '0', STR_PAD_LEFT) : '',
            ];
        }

        $filename = "{$staff->name}_{$year}_{$month}_勤怠一覧.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename*=UTF-8''" . rawurlencode($filename),
        ];

        return new StreamedResponse(function () use ($days) {
            $out =fopen('php://output', 'w');

            fwrite($out, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($out, ['日付', '出勤', '退勤', '休憩', '合計']);

            foreach ($days as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, 200, $headers);
    }
}