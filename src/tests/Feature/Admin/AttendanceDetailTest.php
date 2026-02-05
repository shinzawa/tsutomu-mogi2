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
    private User $user1, $user2, $user3;
    private Attendance $attendance, $attendance2, $attendance3;
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
        $this->attendance = Attendance::factory()->for($user1)->create([
            'id' => 1,
            'work_date' => '2024-01-10',
            'clock_in'   => '2024-01-10 09:00',
            'clock_out'     => '2024-01-10 18:00',
            'note'         => 'テスト備考',
        ]);

        $this->break1 = BreakTime::factory()->for($this->attendance)->create([
            'attendance_id' => '1',
            'start' => '2024-01-10 12:00',
            'end'   => '2024-01-10 13:00',
        ]);

        $this->break2 = BreakTime::factory()->for($this->attendance)->create([
            'attendance_id' => '1',
            'start' => '2024-01-10 12:00',
            'end'   => '2024-01-10 13:00',
        ]);

        $this->break3 = BreakTime::factory()->for($this->attendance)->create([
            'attendance_id' => '1',
            'start' => '2024-01-10 12:00',
            'end'   => '2024-01-10 13:00',
        ]);
    }

    /** @test */
    public function 管理者は勤怠詳細画面を表示できる()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get("/admin/attendance/{$this->attendance->id}");

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
            ->put("/admin/attendance/{$this->attendance->id}", [
                'clock_in'  => '20:00',
                'clock_out'    => '09:00',
                'note'        => 'テスト',
            ]);
        $response->assertStatus(302);

        $response->assertSessionHasErrors([
            'clock_in' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);

        // DB が更新されていないこと
        $this->assertEquals('2024-01-10 09:00:00', $this->attendance->fresh()->clock_in);
    }

    /** @test */
    public function 休憩開始が退勤時間より後の場合はエラーになる()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->put("/admin/attendance/{$this->attendance->id}", [
                'clock_in'  => '09:00',
                'clock_out'    => '18:00',
                'break_start' => ['19:00'],
                'break_end' => ['19:30'],
                'note'        => 'テスト',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'break_start.0' => '休憩時間が不適切な値です',
        ]);

        $this->assertEquals('180', $this->attendance->fresh()->total_break_minutes);
    }

    /** @test */
    public function 休憩終了が退勤時間より後の場合はエラーになる()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->put("/admin/attendance/{$this->attendance->id}", [
                'clock_in'  => '09:00',
                'clock_out'    => '18:00',
                'break_start' => ['17:00'],
                'break_end' => ['19:00'],
                'note'        => 'テスト',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'break_end.0' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);

        $this->assertEquals('180', $this->attendance->fresh()->total_break_minutes);
    }

    /** @test */
    public function 備考が未入力の場合はエラーになる()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->put("/admin/attendance/{$this->attendance->id}", [
                'clock_in'  => '09:00',
                'clock_outend_time'    => '18:00',
                'break_start' => ['12:00'],
                'break_end'   => ['13:00'],
                'note'        => '',
            ]);

        $response->assertSessionHasErrors([
            'note' => '備考を記入してください',
        ]);

        $this->assertEquals('テスト備考', $this->attendance->fresh()->note);
    }
}
