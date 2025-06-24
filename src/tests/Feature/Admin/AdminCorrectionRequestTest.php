<?php

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\CorrectionRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminCorrectionRequestTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_admin_can_see_all_pending_correction_requests()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $attendance1 = Attendance::factory()->create(['user_id' => $user1->id]);
        $attendance2 = Attendance::factory()->create(['user_id' => $user2->id]);

        CorrectionRequest::factory()->create([
            'attendance_id' => $attendance1->id,
            'user_id' => $user1->id,
            'approval_status' => 'pending',
            'remarks' => 'ユーザー1の申請'
        ]);

        CorrectionRequest::factory()->create([
            'attendance_id' => $attendance2->id,
            'user_id' => $user2->id,
            'approval_status' => 'pending',
            'remarks' => 'ユーザー2の申請'
        ]);

        $response = $this->actingAs($admin)->get(route('admin.correction_request.list', ['status' => 'pending']));

        $response->assertSee('ユーザー1の申請');
        $response->assertSee('ユーザー2の申請');
    }

    public function test_admin_can_see_all_approved_correction_requests()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $attendance1 = Attendance::factory()->create(['user_id' => $user1->id]);
        $attendance2 = Attendance::factory()->create(['user_id' => $user2->id]);

        CorrectionRequest::factory()->create([
            'attendance_id' => $attendance1->id,
            'user_id' => $user1->id,
            'approval_status' => 'approved',
            'remarks' => 'ユーザー1承認済み'
        ]);

        CorrectionRequest::factory()->create([
            'attendance_id' => $attendance2->id,
            'user_id' => $user2->id,
            'approval_status' => 'approved',
            'remarks' => 'ユーザー2承認済み'
        ]);

        $response = $this->actingAs($admin)->get(route('admin.correction_request.list', ['status' => 'approved']));

        $response->assertSee('ユーザー1承認済み');
        $response->assertSee('ユーザー2承認済み');
    }

    public function test_admin_can_see_correction_request_detail_content()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        $request = CorrectionRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'approval_status' => 'pending',
            'new_clock_in' => '09:00',
            'new_clock_out' => '17:00',
            'remarks' => '申請内容の確認'
        ]);

        $response = $this->actingAs($admin)->get(route('admin.correction_request.approve_form', ['attendance_correct_request' => $request->id]));

        $response->assertSee($request->new_clock_in)
                ->assertSee($request->new_clock_out)
                ->assertSee($request->remarks);
    }

    public function test_admin_can_approve_correction_request_and_attendance_is_updated()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->format('Y-m-d'),
            'clock_in' => '08:00',
            'clock_out' => '16:00'
        ]);

        $request = CorrectionRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'approval_status' => 'pending',
            'new_clock_in' => '09:00',
            'new_clock_out' => '17:00',
            'remarks' => '申請内容の確認'
        ]);

        $this->actingAs($admin)->post(route('admin.correction_request.approve', ['attendance_correct_request' => $request->id]));

        $this->assertDatabaseHas('correction_requests', [
            'id' => $request->id,
            'approval_status' => 'approved',
            'approved_at' => now()->format('Y-m-d H:i:s')
        ]);
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_in' => '09:00',
            'clock_out' => '17:00'
        ]);
    }
}
