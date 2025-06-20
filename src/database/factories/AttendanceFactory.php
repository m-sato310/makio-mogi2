<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id'   => User::factory(),
            'work_date' => $this->faker->date(),
            'clock_in'  => $this->faker->optional()->time('H:i'),
            'clock_out' => $this->faker->optional()->time('H:i'),
        ];
    }
}
