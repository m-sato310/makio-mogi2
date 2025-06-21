<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceList extends TestCase
{
    use RefreshDatabase;

    public function test_all_of_my_attendance_records_are_displayed_in_list()
    {
        $user = User::factory()->create();


        $attendances = collect([0, 1, 2])->map(function ($i) use ($user) {
            return Attendance::factory()->create([
                'user_id' => $user->id,
                'work_date' => Carbon::now()->startOfMonth()->addDays($i),
                'clock_in' => '09:00',
                'clock_out' => '18:00',
            ]);
        });

        Attendance::factory()->create();

        $response = $this->actingAs($user)->get('/attendance/list');

        $html = $response->getContent();

        $this->assertEquals(3, substr_count($html, '09:00'));
        $this->assertEquals(3, substr_count($html, '18:00'));
    }

    public function test_current_month_is_displayed_on_attendance_list_screen()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendance/list');

        $nowMonth = now()->format('Y/m');
        $response->assertSee($nowMonth);
    }

    public function test_previous_month_records_are_displayed_when_previous_month_button_is_pressed()
    {
        $user = User::factory()->create();

        $lastMonthDate = now()->subMonth();
        $year = $lastMonthDate->year;
        $month = $lastMonthDate->month;

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $lastMonthDate->format('Y-m-d'),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $response = $this->actingAs($user)->get("/attendance/list?year={$year}&month={$month}");

        $response->assertSee('09:00');
        $response->assertSee('18:00');

        $lastMonthLabel = $lastMonthDate->format('Y/m');
        $response->assertSee($lastMonthLabel);
    }

    public function test_next_month_records_are_displayed_when_next_month_button_is_pressed()
    {
        $user = User::factory()->create();

        $nextMonthDate = now()->addMonth();
        $year = $nextMonthDate->year;
        $month = $nextMonthDate->month;

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $nextMonthDate->format('Y-m-d'),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $response = $this->actingAs($user)->get("/attendance/list?year={$year}&month={$month}");

        $response->assertSee('09:00');
        $response->assertSee('18:00');

        $nextMonthLabel = $nextMonthDate->format('Y/m');
        $response->assertSee($nextMonthLabel);
    }

    public function test_clicking_detail_button_navigates_to_attendance_detail_screen()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get('/attendance/' . $attendance->id);

        $response->assertOk();

        $date = Carbon::parse($attendance->work_date);
        $response->assertSee($date->format('Y年'));
        $response->assertSee($date->format('n月j日'));
    }
}
