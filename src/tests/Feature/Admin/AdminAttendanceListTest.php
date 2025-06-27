<?php

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_admin_can_see_all_users_attendance_for_today()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $date = now()->format('Y-m-d');

        $attendance1 = Attendance::factory()->create([
            'user_id' => $user1->id,
            'work_date' => $date,
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $attendance2 = Attendance::factory()->create([
            'user_id' => $user2->id,
            'work_date' => $date,
            'clock_in' => '10:00',
            'clock_out' => '19:00',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.attendance.list', ['date' => $date]));

        $response->assertSee($user1->name)
                ->assertSee($attendance1->clock_in)
                ->assertSee($attendance1->clock_out)
                ->assertSee($user2->name)
                ->assertSee($attendance2->clock_in)
                ->assertSee($attendance2->clock_out);
    }

    public function test_admin_attendance_list_shows_current_date()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $requestDate = now()->format('Y-m-d');
        $displayDate = now()->format('Y年n月j日');

        $response = $this->actingAs($admin)->get(route('admin.attendance.list', ['date' => $requestDate]));

        $response->assertSee($displayDate);
    }

    public function test_admin_attendance_list_shows_previous_day_data()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();
        $previousDate = now()->subDay()->format('Y-m-d');

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $previousDate,
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.attendance.list', ['date' => $previousDate]));

        $response->assertSee($user->name)
                ->assertSee('09:00')
                ->assertSee('18:00')
                ->assertSee(now()->subDay()->format('Y年n月j日'));
    }

    public function test_admin_attendance_list_shows_next_day_data()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();
        $nextDate = now()->addDay()->format('Y-m-d');

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $nextDate,
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.attendance.list', ['date' => $nextDate]));

        $response->assertSee($user->name)
                ->assertSee('09:00')
                ->assertSee('18:00')
                ->assertSee(now()->addDay()->format('Y年n月j日'));
    }
}
