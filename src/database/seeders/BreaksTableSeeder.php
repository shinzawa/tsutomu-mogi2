<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BreakTime;

class BreaksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param = [
            'attendances_id' => 1,
            'break_start' => '2026-01-25 12:00:00',
            'break_end' => '2026-01-25 13:00:00',
        ];
        BreakTime::create($param);
    }
}
