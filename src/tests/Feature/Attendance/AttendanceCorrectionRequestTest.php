<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance;
use App\Models\CorrectionRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceCorrectionRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_error_when_clock_in_is_after_clock_out()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->format('Y-m-d'),
        ]);

        $data = [
            'new_clock_in' => '18:00',
            'new_clock_out' => '17:00',
            'remarks' => 'テスト備考',
        ];

        $response = $this->actingAs($user)->post(route('attendance.correction', ['id' => $attendance->id]), $data);

        $response->assertSessionHasErrors([
            'new_clock_in' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_error_when_break_start_is_after_clock_out()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->format('Y-m-d'),
        ]);

        $data = [
            'new_clock_in' => '09:00',
            'new_clock_out' => '17:00',
            'remarks' => 'テスト備考',
            'new_breaks' => [
                ['new_break_start' => '18:00', 'new_break_end' => '18:30'],
            ],
        ];

        $response = $this->actingAs($user)->post(route('attendance.correction', ['id' => $attendance->id]), $data);

        $response->assertSessionHasErrors([
            'new_breaks.0.new_break_start' => '休憩時間が不適切な値です',
        ]);
    }

    public function test_error_when_break_end_is_after_clock_out()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->format('Y-m-d'),
        ]);

        $data = [
            'new_clock_in' => '09:00',
            'new_clock_out' => '17:00',
            'remarks' => 'テスト備考',
            'new_breaks' => [
                ['new_break_start' => '16:00', 'new_break_end' => '18:00'],
            ],
        ];

        $response = $this->actingAs($user)->post(route('attendance.correction', ['id' => $attendance->id]), $data);

        $response->assertSessionHasErrors([
            'new_breaks.0.new_break_end' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_error_when_remarks_is_empty()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->format('Y-m-d'),
        ]);

        $data = [
            'new_clock_in' => '09:00',
            'new_clock_out' => '17:00',
            'remarks' => '',
        ];

        $response = $this->actingAs($user)->post(route('attendance.correction', ['id' => $attendance->id]), $data);

        $response->assertSessionHasErrors([
            'remarks' => '備考を記入してください',
        ]);
    }

    public function test_correction_request_is_created_and_visible_to_admin()
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->format('Y-m-d'),
        ]);

        $data = [
            'new_clock_in' => '09:00',
            'new_clock_out' => '17:00',
            'remarks' => '修正申請テスト',
        ];

        $this->actingAs($user)->post(route('attendance.correction', ['id' => $attendance->id]), $data);

        $response1 = $this->actingAs($admin)->get(route('correction_request.list'));
        $correctionRequest = CorrectionRequest::where('attendance_id', $attendance->id)->latest()->first();
        $response2 = $this->actingAs($admin)->get(route('admin.correction_request.approve_form', ['attendance_correct_request' => $correctionRequest->id]));

        $response1->assertSee('修正申請テスト');
        $response2->assertSee('修正申請テスト');
    }

    public function test_pending_requests_listed_in_waiting_list_for_user()
    {
        $user = User::factory()->create();

        $dates = [
            now()->format('Y-m-d'),
            now()->addDay()->format('Y-m-d'),
            now()->addDays(2)->format('Y-m-d'),
        ];

        $attendances = collect();
        foreach ($dates as $date) {
            $attendances->push(
                Attendance::factory()->create([
                    'user_id' => $user->id,
                    'work_date' => $date,
                ])
            );
        }

        foreach ($attendances as $attendance) {
            $data = [
                'new_clock_in' => '09:00',
                'new_clock_out' => '17:00',
                'remarks' => '修正申請テスト' . $attendance->id,
            ];
            $this->actingAs($user)->post(route('attendance.correction', ['id' => $attendance->id]), $data);
        }

        $response = $this->actingAs($user)->get(route('correction_request.list', ['tab' => 'pending']));
        foreach ($attendances as $attendance) {
            $response->assertSee('修正申請テスト' . $attendance->id);
        }
    }

    public function test_approved_requests_listed_in_approved_list_for_user()
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);

        $dates = [
            now()->format('Y-m-d'),
            now()->addDay()->format('Y-m-d'),
            now()->addDays(2)->format('Y-m-d'),
        ];

        $attendances = collect();
        foreach ($dates as $date) {
            $attendances->push(
                Attendance::factory()->create([
                    'user_id' => $user->id,
                    'work_date' => $date,
                ])
            );
        }

        $correctionRequestsIds = [];
        foreach ($attendances as $attendance) {
            $data = [
                'new_clock_in' => '09:00',
                'new_clock_out' => '17:00',
                'remarks' => '承認済みテスト' . $attendance->id,
            ];
            $this->actingAs($user)->post(route('attendance.correction', ['id' => $attendance->id]), $data);

            $correctionRequest = CorrectionRequest::where('attendance_id', $attendance->id)->latest()->first();
            $correctionRequestsIds[] = $correctionRequest->id;
        }

        foreach ($correctionRequestsIds as $correctionRequestId) {
            $this->actingAs($admin)->post(route('admin.correction_request.approve', ['attendance_correct_request' => $correctionRequestId]));
        }

        $response = $this->actingAs($user)->get(route('correction_request.list', ['tab' => 'approved']));
        foreach ($attendances as $attendance) {
            $response->assertSee('承認済みテスト' . $attendance->id);
        }
    }

    public function test_clicking_detail_button_navigates_to_correction_request_detail_screen()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->format('Y-m-d'),
        ]);
        $data = [
            'new_clock_in' => '09:00',
            'new_clock_out' => '17:00',
            'remarks' => '詳細画面遷移テスト' . $attendance->id,
        ];
        $this->actingAs($user)->post(route('attendance.correction', ['id' => $attendance->id]), $data);

        $response = $this->actingAs($user)->get(route('correction_request.list', ['tab' => 'pending']));

        $response = $this->actingAs($user)->get(route('attendance.detail', ['id' => $attendance->id]));
        $response->assertOk();
        $response->assertSee('詳細画面遷移テスト' . $attendance->id);
    }
}
