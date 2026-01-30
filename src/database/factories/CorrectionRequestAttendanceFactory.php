<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\CorrectionRequestAttendance;
use App\Models\User;
use App\Models\Attendance;

class CorrectionRequestAttendanceFactory extends Factory
{
    protected $model = CorrectionRequestAttendance::class;

    public function definition()
    {
        return [
            'attendances_id' => Attendance::factory(),
            'user_id' => User::factory(),
            'requested_clock_in' => $this->faker->dateTime(),
            'requested_clock_out' => $this->faker->dateTime(),
            'reason' => 'テスト理由',
            'status' => 'pending',
            'reviewed_by' => null,
            'reviewed_at' => null,
        ];
    }
}
