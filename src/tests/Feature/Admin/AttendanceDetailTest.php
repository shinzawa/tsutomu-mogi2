<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\BreakTime;

use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;
    private User $user1,$user2,$user3;
    private Attendance $attendance1, $attendance2, $attendance3;
    private BreakTime $break1, $break2, $break3;

    protected function setUp(): void
    {
        parent::setUp();

        // 管理者ユーザー作成
        $this->admin = Admin::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();
        // 勤怠データ作成
        $this->attendance1 = Attendance::factory()->for($user1)->create([
            'id' => 1,
            'work_date' => '2024-01-10',
            'clock_in'   => '2024-01-10 09:00',
            'clock_out'     => '2024-01-10 18:00',
            'note'         => 'テスト備考',
        ]);

        $this->attendance2 = Attendance::factory()->for($user2)->create([
            'id' => 2,
            'work_date' => '2024-01-10',
            'clock_in'   => '2024-01-10 09:00',
            'clock_out'     => '2024-01-10 18:00',
            'note'         => 'テスト備考',
        ]);

        $this->attendance3 = Attendance::factory()->for($user3)->create([
            'id' => 3,
            'work_date' => '2024-01-10',
            'clock_in'   => '2024-01-10 09:00',
            'clock_out'     => '2024-01-10 18:00',
            'note'         => 'テスト備考',
        ]);

        $this->break1 = BreakTime::factory()->for($this->attendance1)->create([
            'start' => '2024-01-10 12:00',
            'end'   => '2024-01-10 13:00',
        ]);

        $this->break2 = BreakTime::factory()->for($this->attendance2)->create([
            'start' => '2024-01-10 12:00',
            'end'   => '2024-01-10 13:00',
        ]);

        $this->break3 = BreakTime::factory()->for($this->attendance3)->create([
            'start' => '2024-01-10 12:00',
            'end'   => '2024-01-10 13:00',
        ]);
    }

    /** @test */
    public function 管理者は勤怠詳細画面を表示できる()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get("/admin/attendance/{$this->attendance1->id}");

        $response->assertStatus(200);
        $response->assertSee('2024年');
        $response->assertSee('1月10日');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
        $response->assertSee('テスト備考');
    }

    /** @test */
    public function 出勤時間が退勤時間より後の場合はエラーになる()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->post("/admin/attendance/{$this->attendance1->id}", [
                'start_time'  => '2024-01-10 20:00',
                'end_time'    => '2024-01-10 09:00',
                'note'        => 'テスト',
            ]);

        $response->assertSessionHasErrors([
            'start_time' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);

        // DB が更新されていないこと
        $this->assertEquals('2024-01-10 09:00', $this->attendance1->fresh()->start_time);
    }

    /** @test */
    public function 休憩開始が退勤時間より後の場合はエラーになる()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->post("/admin/attendance/{$this->attendance1->id}", [
                'start_time'  => '2024-01-10 09:00',
                'end_time'    => '2024-01-10 18:00',
                'note'        => 'テスト',
            ]);

        $response->assertSessionHasErrors([
            'break_start' => '休憩時間が不適切な値です',
        ]);

        $this->assertEquals('12:00', $this->attendance->fresh()->break_start);
    }

    /** @test */
    public function 休憩終了が退勤時間より後の場合はエラーになる()
    {
        $response = $this->actingAs($this->admin)
            ->post("/admin/attendance/{$this->attendance->id}", [
                'start_time'  => '09:00',
                'end_time'    => '18:00',
                'break_start' => '12:00',
                'break_end'   => '20:00',
                'note'        => 'テスト',
            ]);

        $response->assertSessionHasErrors([
            'break_end' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);

        $this->assertEquals('13:00', $this->attendance->fresh()->break_end);
    }

    /** @test */
    public function 備考が未入力の場合はエラーになる()
    {
        $response = $this->actingAs($this->admin)
            ->post("/admin/attendance/{$this->attendance->id}", [
                'start_time'  => '09:00',
                'end_time'    => '18:00',
                'break_start' => '12:00',
                'break_end'   => '13:00',
                'note'        => '',
            ]);

        $response->assertSessionHasErrors([
            'note' => '備考を記入してください',
        ]);

        $this->assertEquals('テスト備考', $this->attendance->fresh()->note);
    }
}
