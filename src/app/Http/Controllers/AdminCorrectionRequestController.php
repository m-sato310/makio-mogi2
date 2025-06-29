<?php

namespace App\Http\Controllers;

use App\Models\CorrectionRequest;
use Illuminate\Support\Facades\DB;

class AdminCorrectionRequestController extends Controller
{
    public function showApproveRequestForm($attendance_correct_request)
    {
        $request = CorrectionRequest::with(['user', 'attendance.workBreaks', 'correctionBreaks'])->findOrFail($attendance_correct_request);
        $attendance = $request->attendance;

        if ($request->correctionBreaks->isNotEmpty()) {
            $breaks = $request->correctionBreaks;
        } else {
            $breaks = collect();
        }

        $isApproved = $request->approval_status === 'approved';

        return view('admin.attendance.detail', compact(
            'request',
            'attendance',
            'breaks',
            'isApproved'
        ));
    }

    public function approveRequest($attendance_correct_request)
    {
        DB::transaction(function () use ($attendance_correct_request) {
            $correction = CorrectionRequest::with(['attendance', 'correctionBreaks'])->findOrFail($attendance_correct_request);

            $attendance = $correction->attendance;
            $attendance->update([
                'clock_in' => $correction->new_clock_in,
                'clock_out' => $correction->new_clock_out,
            ]);

            $attendance->workBreaks()->delete();
            foreach ($correction->correctionBreaks as $break) {
                $attendance->workBreaks()->create([
                    'break_start' => $break->new_break_start,
                    'break_end' => $break->new_break_end,
                ]);
            }

            $correction->update([
                'approval_status' => 'approved',
                'approved_at' => now(),
            ]);
        });

        return redirect()->route('correction_request.list');
    }
}
