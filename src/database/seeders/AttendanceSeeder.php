<?php

namespace Database\Seeders;

use App\Models\Attendance;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $userIds = range(1, 5);

        $aprilWorkdays = [1, 2, 3, 4, 7, 8, 9, 10, 11, 14, 15, 16, 17, 18, 21, 22, 23, 24, 25, 28];
        $mayWorkdays   = [1, 2, 7, 8, 9, 12, 13, 14, 15, 16, 19, 20, 21, 22, 23, 26, 27, 28, 29, 30];
        $juneWorkdays  = [2, 3];

        foreach ($userIds as $userId) {
            foreach ($aprilWorkdays as $day) {
                Attendance::create([
                    'user_id'   => $userId,
                    'work_date' => "2025-04-" . str_pad($day, 2, '0', STR_PAD_LEFT),
                    'clock_in'  => '09:00',
                    'clock_out' => '17:00',
                ]);
            }
            foreach ($mayWorkdays as $day) {
                Attendance::create([
                    'user_id'   => $userId,
                    'work_date' => "2025-05-" . str_pad($day, 2, '0', STR_PAD_LEFT),
                    'clock_in'  => '09:00',
                    'clock_out' => '17:00',
                ]);
            }
            foreach ($juneWorkdays as $day) {
                Attendance::create([
                    'user_id'   => $userId,
                    'work_date' => "2025-06-" . str_pad($day, 2, '0', STR_PAD_LEFT),
                    'clock_in'  => '09:00',
                    'clock_out' => '17:00',
                ]);
            }
        }
    }
}
