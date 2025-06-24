<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CorrectionRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'attendance_id'   => Attendance::factory(),
            'user_id'         => User::factory(),
            'new_clock_in'    => $this->faker->time('H:i'),
            'new_clock_out'   => $this->faker->time('H:i'),
            'remarks'         => $this->faker->sentence(6),
            'approval_status' => 'pending',
            'approved_at'     => null,
        ];
    }
}
