<?php

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_admin_attendance_detail_screen_shows_selected_data()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->format('Y-m-d'),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $response = $this->actingAs($admin)->get(route('attendance.detail', ['id' => $attendance->id]));

        $response->assertSee($user->name)
                ->assertSee(now()->format('Y年'))
                ->assertSee(now()->format('n月j日'))
                ->assertSee($attendance->clock_in)
                ->assertSee($attendance->clock_out);
    }

    public function test_admin_gets_error_when_new_clock_in_is_after_new_clock_out()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => now()->format('Y-m-d'),
            'clock_in'  => '09:00',
            'clock_out' => '18:00',
        ]);

        $data = [
            'new_clock_in' => '19:00',
            'new_clock_out' => '10:00',
            'remarks' => 'テスト備考'
        ];

        $response = $this->actingAs($admin)->post(route('admin.attendance.update', ['id' => $attendance->id]), $data);

        $response->assertSessionHasErrors([
            'new_clock_in' => '出勤時間もしくは退勤時間が不適切な値です',
            'new_clock_out' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_admin_gets_error_when_break_start_is_after_new_clock_out()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => now()->format('Y-m-d'),
            'clock_in'  => '09:00',
            'clock_out' => '18:00',
        ]);

        $data = [
            'new_clock_in' => '09:00',
            'new_clock_out' => '18:00',
            'new_breaks' => [
                ['new_break_start' => '19:00', 'new_break_end' => '20:00'],
            ],
            'remarks' => 'テスト備考'
        ];

        $response = $this->actingAs($admin)->post(route('admin.attendance.update', ['id' => $attendance->id]), $data);

        $response->assertSessionHasErrors([
            'new_breaks.0.new_break_start' => '休憩時間が不適切な値です',
        ]);
    }

    public function test_admin_gets_error_when_break_end_is_after_new_clock_out()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => now()->format('Y-m-d'),
            'clock_in'  => '09:00',
            'clock_out' => '18:00',
        ]);

        $data = [
            'new_clock_in' => '09:00',
            'new_clock_out' => '18:00',
            'new_breaks' => [
                ['new_break_start' => '17:00', 'new_break_end' => '20:00'],
            ],
            'remarks' => 'テスト備考'
        ];

        $response = $this->actingAs($admin)->post(route('admin.attendance.update', ['id' => $attendance->id]), $data);

        $response->assertSessionHasErrors([
            'new_breaks.0.new_break_end' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_admin_gets_error_when_remarks_is_empty()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => now()->format('Y-m-d'),
            'clock_in'  => '09:00',
            'clock_out' => '18:00',
        ]);

        $data = [
            'new_clock_in' => '09:00',
            'new_clock_out' => '18:00',
            'remarks' => '',
        ];

        $response = $this->actingAs($admin)->post(route('admin.attendance.update', ['id' => $attendance->id]), $data);

        $response->assertSessionHasErrors([
            'remarks' => '備考を記入してください',
        ]);
    }
}
