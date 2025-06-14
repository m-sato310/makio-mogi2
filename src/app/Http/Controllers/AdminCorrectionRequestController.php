<?php

namespace App\Http\Controllers;

use App\Models\CorrectionRequest;
use Illuminate\Http\Request;

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
}
