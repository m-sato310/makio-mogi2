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
        $attendance = Attendance::with(['user', 'workBreaks', 'correctionRequests'])->findOrFail($id);

        $isPending = false;

        $correctionRequest = $attendance->correctionRequests()->orderByDesc('created_at')->first();

        $breaks = $attendance->workBreaks;

        return view('attendance.detail', compact('attendance', 'isPending', 'correctionRequest', 'breaks'))->with('isAdmin', true);
    }

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

        return redirect()->route('admin.attendance.detail', ['id' => $id]);
    }
}
