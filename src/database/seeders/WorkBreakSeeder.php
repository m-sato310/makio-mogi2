<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\WorkBreak;
use Illuminate\Database\Seeder;

class WorkBreakSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $attendances = Attendance::all();

        foreach ($attendances as $attendance) {
            WorkBreak::create([
                'attendance_id' => $attendance->id,
                'break_start' => '12:00',
                'break_end' => '13:00',
            ]);
        }
    }
}
