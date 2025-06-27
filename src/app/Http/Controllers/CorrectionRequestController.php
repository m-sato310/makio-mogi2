<?php

namespace App\Http\Controllers;

use App\Http\Requests\CorrectionRequestRequest;
use App\Models\Attendance;
use App\Models\CorrectionBreak;
use App\Models\CorrectionRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CorrectionRequestController extends Controller
{
    public function applyCorrectionRequest(CorrectionRequestRequest $request, $attendanceId)
    {
        $attendance = Attendance::where('id', $attendanceId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($attendance->correctionRequests()->where('approval_status', 'pending')->exists()) {
            return redirect()->route('attendance.detail', ['id' => $attendanceId])
                ->withErrors(['すでに承認待ちの修正申請があります']);
        }

        DB::beginTransaction();
        try {
            $correctionRequest = CorrectionRequest::create([
                'attendance_id' => $attendance->id,
                'user_id' => Auth::id(),
                'new_clock_in' => $request->new_clock_in,
                'new_clock_out' => $request->new_clock_out,
                'remarks' => $request->remarks,
                'approval_status' => 'pending',
            ]);

            $breaks = $request->input('new_breaks', []);
            foreach ($breaks as $break) {
                if (!empty($break['new_break_start']) && !empty($break['new_break_end'])) {
                    CorrectionBreak::create([
                        'correction_request_id' => $correctionRequest->id,
                        'new_break_start' => $break['new_break_start'],
                        'new_break_end' => $break['new_break_end'],
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('attendance.detail', ['id' => $attendanceId])
                ->with('status', '修正申請を受け付けました。管理者の承認をお待ちください。');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('attendance.detail', ['id' => $attendanceId])
                ->withErrors(['エラーが発生しました。再度お試しください。', $e->getMessage()]);
        }
    }

    // public function listMyApplications()
    // {
    //     $userId = Auth::id();

    //     $pendingList = CorrectionRequest::with('attendance', 'user')
    //         ->where('user_id', $userId)
    //         ->where('approval_status', 'pending')
    //         ->orderByDesc('created_at')
    //         ->get();

    //     $approvedList = CorrectionRequest::with('attendance', 'user')
    //         ->where('user_id', $userId)
    //         ->where('approval_status', 'approved')
    //         ->orderByDesc('created_at')
    //         ->get();

    //     return view('correction_request.list', compact('pendingList', 'approvedList'));
    // }

    public function listApplications(Request $request)
    {
        $user = Auth::user();
        $status = $request->input('status', 'pending');

        if ($user->is_admin) {
            // 管理者：全ユーザー分
            $requests = CorrectionRequest::with('attendance', 'user')
                ->where('approval_status', $status)
                ->orderByDesc('created_at')
                ->get();

            return view('admin.correction_request.list', compact('requests', 'status'));
        } else {
            // 一般ユーザー：自分の分だけ
            $pendingList = CorrectionRequest::with('attendance', 'user')
                ->where('user_id', $user->id)
                ->where('approval_status', 'pending')
                ->orderByDesc('created_at')
                ->get();

            $approvedList = CorrectionRequest::with('attendance', 'user')
                ->where('user_id', $user->id)
                ->where('approval_status', 'approved')
                ->orderByDesc('created_at')
                ->get();

            return view('correction_request.list', compact('pendingList', 'approvedList'));
        }
    }
}
