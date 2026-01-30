<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\CorrectionRequestBreakTime;
use App\Models\CorrectionRequestAttendance;

class CorrectionRequestBreakTimeFactory extends Factory
{
    protected $model = CorrectionRequestBreakTime::class;

    public function definition()
    {
        return [
            'request_id' => CorrectionRequestAttendance::factory(),
            'start' => $this->faker->dateTime(),
            'end' => $this->faker->dateTime(),
        ];
    }
}
