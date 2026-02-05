<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CorrectionRequestAttendance;

class CorrectionRequestAttendancesTableSeeder extends Seeder
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
            'user_id' => 1,
            'requested_clock_in' => '2026-02-05 09:00:00',
            'requested_clock_out' => '2026-02-05 18:00:00',
            'reason' => '遅延のため',
            'status' => 'pending',
            'reviewed_by' => 1,
            'reviewed_at' => null,
        ];
        CorrectionRequestAttendance::create($param);
        $param = [
            'attendances_id' => 24,
            'user_id' => 2,
            'requested_clock_in' => '2026-02-05 09:00:00',
            'requested_clock_out' => '2026-02-05 18:00:00',
            'reason' => '遅延のため',
            'status' => 'pending',
            'reviewed_by' => 1,
            'reviewed_at' => null,
        ];
        CorrectionRequestAttendance::create($param);
        $param = [
            'attendances_id' => 47,
            'user_id' => 3,
            'requested_clock_in' => '2026-02-05 09:00:00',
            'requested_clock_out' => '2026-02-05 18:00:00',
            'reason' => '遅延のため',
            'status' => 'pending',
            'reviewed_by' => 1,
            'reviewed_at' => null,
        ];
        CorrectionRequestAttendance::create($param);        $param = [
            'attendances_id' => 70,
            'user_id' => 4,
            'requested_clock_in' => '2026-02-05 09:00:00',
            'requested_clock_out' => '2026-02-05 18:00:00',
            'reason' => '遅延のため',
            'status' => 'pending',
            'reviewed_by' => 1,
            'reviewed_at' => null,
        ];
        CorrectionRequestAttendance::create($param);
        $param = [
            'attendances_id' => 93,
            'user_id' => 5,
            'requested_clock_in' => '2026-02-05 09:00:00',
            'requested_clock_out' => '2026-02-05 18:00:00',
            'reason' => '遅延のため',
            'status' => 'pending',
            'reviewed_by' => 1,
            'reviewed_at' => null,
        ];
        CorrectionRequestAttendance::create($param);
        $param = [
            'attendances_id' => 116,
            'user_id' => 6,
            'requested_clock_in' => '2026-02-05 09:00:00',
            'requested_clock_out' => '2026-02-05 18:00:00',
            'reason' => '遅延のため',
            'status' => 'pending',
            'reviewed_by' => 1,
            'reviewed_at' => null,
        ];
        CorrectionRequestAttendance::create($param);
    }
}
