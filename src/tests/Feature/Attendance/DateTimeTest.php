<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DateTimeTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_attendance_screen_displays_current_datetime()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $now = now()->format('Y/m/d H:i');
        $response = $this->get('/attendance');
        $response->assertSee($now);
    }
}
