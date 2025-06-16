<?php

namespace App\Http\Controllers;

use App\Models\CorrectionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminCorrectionRequestController extends Controller
{
    public function listAllApplications(Request $request)
    {
        $status = $request->input('status', 'pending');

        if ($status === 'approved') {
            $requests = CorrectionRequest::with('user', 'attendance')
                ->where('approval_status', 'approved')
                ->orderByDesc('approved_at')
                ->get();
        } else {
            $requests = CorrectionRequest::with('user', 'attendance')
                ->where('approval_status', 'pending')
                ->orderByDesc('created_at')
                ->get();
        }

        return view('admin.correction_request.list', compact('requests', 'status'));
    }

    public function showApproveRequestForm($id)
    {
        $request = CorrectionRequest::with(['user', 'attendance.workBreaks', 'correctionBreaks'])->findOrFail($id);

        $attendance = $request->attendance;

        $breaks = $request->correctionBreaks->isNotEmpty()
            ? $request->correctionBreaks
            : ($attendance->workBreaks ?? collect());

        $isApproved = $request->approval_status === 'approved';

        return view('admin.attendance.detail', compact(
            'request',
            'attendance',
            'breaks',
            'isApproved'
        ));
    }

    public function approveRequest(Request $request, $id)
    {
        DB::transaction(function () use ($id) {
            $correction = CorrectionRequest::with(['attendance', 'correctionBreaks'])->findOrFail($id);

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

        return redirect()->route('admin.correction_request.list');
    }
}
