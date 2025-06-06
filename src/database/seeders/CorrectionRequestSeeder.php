<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\CorrectionRequest;
use Illuminate\Database\Seeder;

class CorrectionRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (range(1, 5) as $userId) {
            $attendances = Attendance::where('user_id', $userId)
                ->orderByDesc('work_date')->take(3)->get();

            foreach ($attendances as $i => $attendance) {
                CorrectionRequest::create([
                    'attendance_id' => $attendance->id,
                    'user_id' => $userId,
                    'new_clock_in' => $attendance->clock_in === '09:00' ? '09:10' : $attendance->clock_in,
                    'new_clock_out' => $attendance->clock_out === '17:00' ? '17:00' : $attendance->clock_out,
                    'remarks' => $i === 0 ? '交通渋滞のため10分遅刻' : '家庭の事情で遅刻',
                    'approval_status' => $i % 2 === 0 ? 'pending' : 'approved',
                ]);
            }
        }
    }
}
