<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_attendance_clock_in_button_works_and_status_changes()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('出勤');

        $response = $this->actingAs($user)->post('/attendance/start');
        $response->assertRedirect('/attendance');

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('出勤中');
    }

    public function test_attendance_clock_in_button_is_hidden_after_already_clocked_in_and_out()
    {
        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subHours(8)->format('H:i'),
            'clock_out' => now()->subHours(1)->format('H:i')
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertDontSee('出勤');
    }

    public function test_attendance_clock_in_time_is_recorded_and_shown_on_admin_screen()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/attendance/start');

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', now()->toDateString())
            ->first();

        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get('/admin/attendance/list');
        $response->assertSee(\Carbon\Carbon::createFromFormat('H:i:s', $attendance->clock_in)->format('H:i'));
    }
}
