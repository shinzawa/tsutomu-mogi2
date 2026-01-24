<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;

class AttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param = [
            'user_id' => 1,
            'work_date' => '2026-01-25',
            'clock_in' => '2026-01-25 08:00:00',
            'clock_out' => '2026-01-25 18:00:00',
            'total_break_minutes' => '60',
        ];
        Attendance::create($param);
    }
}
