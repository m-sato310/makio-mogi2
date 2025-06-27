<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class BreakTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_break_start_button_works_and_status_changes()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subHours(8)->format('H:i'),
            'clock_out' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩入');

        $response = $this->actingAs($user)->post('/attendance/break/start', [
            'attendance_id' => $attendance->id,
        ]);
        $response->assertRedirect('/attendance');

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩中');
    }

    public function test_break_start_button_can_be_pressed_multiple_times_per_day()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subHours(3)->format('H:i'),
            'clock_out' => null,
        ]);

        $this->actingAs($user)->post('/attendance/break/start', [
            'attendance_id' => $attendance->id,
        ]);
        $this->actingAs($user)->post('/attendance/break/end', [
            'attendance_id' => $attendance->id,
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩入');
    }

    public function test_break_end_button_works_and_status_changes_to_on_duty()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subHours(2)->format('H:i'),
            'clock_out' => null,
        ]);

        $this->actingAs($user)->post('/attendance/break/start', [
            'attendance_id' => $attendance->id,
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩戻');

        $this->actingAs($user)->post('/attendance/break/end', [
            'attendance_id' => $attendance->id,
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('出勤中');

    }

    public function test_break_end_button_can_be_pressed_multiple_times_per_day()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subHours(3)->format('H:i'),
            'clock_out' => null,
        ]);

        $this->actingAs($user)->post('/attendance/break/start', [
            'attendance_id' => $attendance->id,
        ]);
        $this->actingAs($user)->post('/attendance/break/end', [
            'attendance_id' => $attendance->id,
        ]);

        $this->actingAs($user)->post('/attendance/break/start', [
            'attendance_id' => $attendance->id,
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩戻');
    }

    public function test_break_times_are_recorded_and_shown_in_attendance_list()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subHours(3)->format('H:i'),
            'clock_out' => null,
        ]);

        $this->actingAs($user)->post('/attendance/break/start', [
            'attendance_id' => $attendance->id,
        ]);
        $this->actingAs($user)->post('/attendance/break/end', [
            'attendance_id' => $attendance->id,
        ]);

        $break = $attendance->workBreaks()->latest('id')->first();
        $breakMinutes = 0;
        if ($break->break_start && $break->break_end) {
            $start = Carbon::createFromFormat('H:i:s', $break->break_start);
            $end = Carbon::createFromFormat('H:i:s', $break->break_end);
            $breakMinutes = $start->diffInMinutes($end);
        }
        $breakTotal = floor($breakMinutes/60) . ':' . str_pad($breakMinutes%60, 2, '0', STR_PAD_LEFT);

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertSee($breakTotal);
    }
}
