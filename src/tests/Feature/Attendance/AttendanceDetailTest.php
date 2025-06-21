<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance;
use App\Models\User;
use App\Models\WorkBreak;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    public function test_attendance_detail_screen_displays_logged_in_user_name()
    {
        $user = User::factory()->create([
            'name' => '山田太郎',
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($user)->get('/attendance/' . $attendance->id);

        $response->assertSee('山田太郎');
    }

    public function test_attendance_detail_screen_displays_selected_attendance_date()
    {
        $user = User::factory()->create();

        $targetDate = now()->subDays(2)->format('Y-m-d');
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $targetDate,
        ]);

        $response = $this->actingAs($user)->get('/attendance/' . $attendance->id);

        $year = now()->subDays(2)->format('Y');
        $monthDay = now()->subDays(2)->format('n月j日');
        $response->assertSee($year . '年');
        $response->assertSee($monthDay);
    }

    public function test_attendance_detail_screen_displays_correct_clock_in_and_out_time()
    {
        $user = User::factory()->create();

        $clockIn = '09:00';
        $clockOut = '18:00';
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->format('Y-m-d'),
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
        ]);

        $response = $this->actingAs($user)->get('/attendance/' . $attendance->id);

        $response->assertSee($clockIn)->assertSee($clockOut);
    }

    public function test_attendance_detail_screen_displays_correct_break_times()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->format('Y-m-d'),
        ]);

        $breakStart = '12:00';
        $breakEnd = '13:00';

        WorkBreak::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => $breakStart,
            'break_end' => $breakEnd,
        ]);

        $response = $this->actingAs($user)->get('/attendance/' . $attendance->id);

        $response->assertSee($breakStart)->assertSee($breakEnd);
    }
}
