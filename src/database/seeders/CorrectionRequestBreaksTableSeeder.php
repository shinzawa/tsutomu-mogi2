<?php

namespace Database\Seeders;
use App\Models\CorrectionRequestBreakTime;
use Illuminate\Database\Seeder;

class CorrectionRequestBreaksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param = [
            'request_id' =>  1,
            'start' => '2026-01-05 12:30:00',
            'end' => '2026-01-05 13:00:00',
        ];
        CorrectionRequestBreakTime::create($param);
        $param = [
            'request_id' =>  2,
            'start' => '2026-01-05 12:30:00',
            'end' => '2026-01-05 13:00:00',
        ];
        CorrectionRequestBreakTime::create($param);
        $param = [
            'request_id' =>  3,
            'start' => '2026-01-05 12:30:00',
            'end' => '2026-01-05 13:00:00',
        ];
        CorrectionRequestBreakTime::create($param);
        $param = [
            'request_id' =>  4,
            'start' => '2026-01-05 12:30:00',
            'end' => '2026-01-05 13:00:00',
        ];
        CorrectionRequestBreakTime::create($param);
        $param = [
            'request_id' =>  5,
            'start' => '2026-01-05 12:30:00',
            'end' => '2026-01-05 13:00:00',
        ];
        CorrectionRequestBreakTime::create($param);
        $param = [
            'request_id' =>  6,
            'start' => '2026-01-05 12:30:00',
            'end' => '2026-01-05 13:00:00',
        ];
        CorrectionRequestBreakTime::create($param);
    }
}
