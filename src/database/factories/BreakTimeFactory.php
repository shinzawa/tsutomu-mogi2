<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\BreakTime;
use App\Models\Attendance;

class BreakTimeFactory extends Factory
{
    protected $model = BreakTime::class;

    public function definition()
    {
        return [
            'attendance_id' => Attendance::factory(),
            'start' => '2024-01-10 12:00',
            'end'   => '2024-01-10 13:00',
        ];
    }
}
