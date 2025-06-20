<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance;
use App\Models\User;
use App\Models\WorkBreak;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StatusTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_attendance_status_is_shown_as_off_duty()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('勤務外');
    }

    public function test_attendance_status_is_shown_as_on_duty()
    {
        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->format('H:i'),
            'clock_out' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('出勤中');
    }

    public function test_attendance_status_is_shown_as_on_break()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subHour(2)->format('H:i'),
            'clock_out' => null,
        ]);

        WorkBreak::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => now()->subMinutes(15)->format('H:i'),
            'break_end' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('休憩中');
    }

    public function test_attendance_status_is_shown_as_finished()
    {
        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subHours(8)->format('H:i'),
            'clock_out' => now()->subMinutes(1)->format('H:i'),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('退勤済');
    }
}
