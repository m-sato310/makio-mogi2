<?php

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserListTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_admin_can_see_all_users_name_and_email()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $user1 = User::factory()->create(['is_admin' => false, 'name' => '山田太郎', 'email' => 'taro@example.com']);
        $user2 = User::factory()->create(['is_admin' => false, 'name' => '鈴木花子', 'email' => 'hanako@example.com']);

        $response = $this->actingAs($admin)->get(route('admin.staff.list'));

        $response->assertSee('山田太郎')
                ->assertSee('taro@example.com')
                ->assertSee('鈴木花子')
                ->assertSee('hanako@example.com');
    }

    public function test_admin_can_see_selected_user_attendance_list()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['name' => '山田太郎']);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->startOfMonth()->format('Y-m-d'),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);
        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->startOfMonth()->addDay()->format('Y-m-d'),
            'clock_in' => '10:00',
            'clock_out' => '19:00',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.attendance.staff', ['id' => $user->id]));

        $response->assertSee($user->name)
                ->assertSee('09:00')
                ->assertSee('18:00')
                ->assertSee('10:00')
                ->assertSee('19:00');
    }

    public function test_admin_can_see_previous_month_attendance_list_for_user()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();

        $prevMonth = now()->subMonth();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $prevMonth->startOfMonth()->format('Y-m-d'),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.attendance.staff', [
            'id' => $user->id,
            'year' => $prevMonth->year,
            'month' => $prevMonth->month,
        ]));

        $response->assertSee($attendance->clock_in)
                ->assertSee($attendance->clock_out)
                ->assertSee($prevMonth->format('Y/m'));
    }

    public function test_admin_can_see_next_month_attendance_list_for_user()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();

        $nextMonth = now()->addMonth();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $nextMonth->startOfMonth()->format('Y-m-d'),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.attendance.staff', [
            'id' => $user->id,
            'year' => $nextMonth->year,
            'month' => $nextMonth->month,
        ]));

        $response->assertSee($attendance->clock_in)
                ->assertSee($attendance->clock_out)
                ->assertSee($nextMonth->format('Y/m'));
    }

    public function test_admin_can_navigate_to_attendance_detail_screen_from_list()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->format('Y-m-d'),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.attendance.staff', ['id' => $user->id]));

        $detailUrl = route('admin.attendance.detail', ['id' => $attendance->id]);
        $response->assertSee($detailUrl);

        $detailResponse = $this->actingAs($admin)->get($detailUrl);
        $detailResponse->assertSee($user->name)
                      ->assertSee(now()->format('Y年'))
                      ->assertSee(now()->format('n月j日'))
                      ->assertSee($attendance->clock_in)
                      ->assertSee($attendance->clock_out);
    }
}