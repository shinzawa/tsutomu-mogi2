<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\CorrectionRequestAttendance;
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
        $attendance = Attendance::factory()->create(['work_date' => '2025-02-01', 'clock_out' => '2025-02-01 17:00']);
        $request = CorrectionRequestAttendance::factory()->create([
            'attendances_id' => $attendance->id,
            'status' => 'pending',
            'requested_clock_out' => '18:00', // 修正後の希望時間
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
    }

    /** @test */
    public function test_admin_can_see_pending_requests_only_in_pending_tab()
    {
        $pendingRequest = CorrectionRequestAttendance::factory()->create(['status' => 'pending']);
        $approvedRequest = CorrectionRequestAttendance::factory()->create(['status' => 'approved']);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.stamp_correction.index', ['status' => 'pending']));

        $response->assertStatus(200);
        $response->assertSee($pendingRequest->reason); // 承認待ちは表示される
        $response->assertDontSee($approvedRequest->reason); // 承認済みは表示されない
    }

    /** @test */
    public function test_admin_can_see_approved_requests_only_in_approved_tab()
    {
        $pendingRequest = CorrectionRequestAttendance::factory()->create(['status' => 'pending']);
        $approvedRequest = CorrectionRequestAttendance::factory()->create(['status' => 'approved']);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.stamp_correction.index', ['status' => 'approved']));

        $response->assertStatus(200);
        $response->assertSee($approvedRequest->reason); // 承認済みは表示される
        $response->assertDontSee($pendingRequest->reason); // 承認待ちは表示されない
    }
}
