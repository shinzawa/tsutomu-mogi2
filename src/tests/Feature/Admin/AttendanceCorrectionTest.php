<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\CorrectionRequestAttendance;
use App\Models\CorrectionRequestBreakTime;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceCorrectionTest extends TestCase
{
    use RefreshDatabase; // テストごとにDBをリセット

    protected $admin;
    protected $user;
    protected $user1;
    protected $user2;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::create(2025, 02, 01));

        $this->admin = Admin::factory()->create();
        $this->user = User::factory()->create();
        $this->user1 = User::factory()->create();
        $this->user2 = User::factory()->create();
        // ユーザー１の今月
        Attendance::factory()->for($this->user)->create([
            'work_date' => '2026-02-05',
            'clock_in' => '2026-02-05 09:00:00',
            'clock_out' => '2026-02-05 18:00:00',
        ]);
        // ユーザー１の前月
        Attendance::factory()->for($this->user)->create([
            'work_date' => '2026-01-05',
            'clock_in' => '2026-01-05 09:00:00',
            'clock_out' => '2026-01-05 18:00:00',
        ]);
    }
    /**
     * 修正申請の承認処理が正しく行われ、勤怠情報が更新されることを確認
     */
    /** @test */
    public function test_admin_can_approve_correction_request()
    {
        // 1. 前準備：管理者、一般ユーザー、申請データを作成
        $attendance = Attendance::factory()->create([
            'work_date' => '2025-02-01',
            'clock_out' => '2025-02-01 17:00'
        ]);
        $break1 = BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'start' => '2025-02-01 12:00',
            'end' => '2025-02-01 13:00'
        ]);
        $break2 = BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'start' => '2025-02-01 15:00',
            'end' => '2025-02-01 15:30'
        ]);

        $request = CorrectionRequestAttendance::factory()->create([
            'attendances_id' => $attendance->id,
            'status' => 'pending',
            'requested_clock_out' => '18:00', // 修正後の希望時間
        ]);

        $requsetBreak1 = CorrectionRequestBreakTime::factory()->create([
            'request_id' => $request->id,
            'start' => '2025-02-01 12:00:00',
            'end' => '2025-02-01 13:00:00'
        ]);

        $requsetBreak2 = CorrectionRequestBreakTime::factory()->create([
            'request_id' => $request->id,
            'start' => '2025-02-01 14:00:00',
            'end' => '2025-02-01 14:30:00'
        ]);

        // 2. 実行：管理者としてログインし、承認API（またはRoute）を叩く
        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.stamp_correction.approve', ['attendance_correct_request_id' => $request->id]));

        // 3. 検証：ステータスコードとDBの状態を確認
        $response->assertRedirect(); // または assertStatus(200)

        // 申請ステータスが「approved」に更新されているか
        $this->assertDatabaseHas('correction_request_attendances', [
            'id' => $request->id,
            'status' => 'approved',
        ]);

        // 元の勤怠データの退勤時間が「18:00」に更新されているか
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_out' => '2025-02-01 18:00:00',
        ]);
        // 元の勤怠データの休憩時刻が、申請通りになっているか?
        $this->assertDatabaseHas('breaks', [
            'id' => 3,
            'attendance_id' => $attendance->id,
            'start' => '2025-02-01 12:00:00',
            'end' => '2025-02-01 13:00:00'
        ]);
        $this->assertDatabaseHas('breaks', [
            'id' => 4,
            'attendance_id' => $attendance->id,
            'start' => '2025-02-01 14:00:00',
            'end' => '2025-02-01 14:30:00'
        ]);
    }
}
