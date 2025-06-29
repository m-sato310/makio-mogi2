<?php

namespace App\Http\Controllers;

use App\Models\User;

class AdminStaffController extends Controller
{
    public function listStaffs()
    {
        $staffs = User::where('is_admin', false)
            ->orderBy('name')
            ->get();

        return view('admin.staff.list', compact('staffs'));
    }
}
