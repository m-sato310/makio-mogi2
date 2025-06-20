<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;

class FinishTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_attendance_finish_button_works_and_status_changes()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subHours(8)->format('H:i'),
            'clock_out' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('退勤');

        $response = $this->actingAs($user)->post('/attendance/finish', [
            'attendance_id' => $attendance->id,
        ]);
        $response->assertRedirect('/attendance');

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('退勤済');
    }

    public function test_attendance_finish_time_is_recorded_and_shown_on_admin_screen()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/attendance/start');

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', now()->toDateString())
            ->first();
        $this->actingAs($user)->post('/attendance/finish', [
            'attendance_id' => $attendance->id,
        ]);

        $admin = User::factory()->create(['is_admin' => true]);

        $attendance->refresh();
        $response = $this->actingAs($admin)->get('/admin/attendance/list');
        $response->assertSee(Carbon::createFromFormat('H:i:s', $attendance->clock_out)->format('H:i'));
    }
}
