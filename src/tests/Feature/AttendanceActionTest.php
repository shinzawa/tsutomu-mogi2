<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceActionTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    /* ============================================================
        出勤機能
    ============================================================ */

    /** @test */
    public function 出勤ボタンが正しく表示され_出勤処理後に勤務中になる()
    {
        $user = User::factory()->create([
            'status' => '勤務外',
        ]);

        // 出勤ボタンが表示される
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('出勤');

        // 出勤処理
        $response = $this->actingAs($user)->post('/attendance/updateStatus', [
            'action' => 'clock_in',
        ]);

        $user->refresh();
        $this->assertEquals('出勤中', $user->status);

        // 再表示で勤務中が見える
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('出勤中');
    }

    /** @test */
    public function 出勤は一日一回のみで_退勤済ユーザーには出勤ボタンが表示されない()
    {
        $user = User::factory()->create([
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($user)->get('/attendance/record');

        // 出勤ボタンが表示されない
        $response->assertDontSee('出勤');
    }

    /** @test */
    public function 出勤時刻が勤怠一覧画面で確認できる()
    {
        Carbon::setTestNow('2024-01-01 09:00:00');

        $user = User::factory()->create([
            'status' => '勤務外',
        ]);

        // 出勤処理
        $this->actingAs($user)->post('/attendance/updateStatus', [
            'action' => 'clock_in',
        ]);

        // 勤怠一覧画面
        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('09:00');
    }


    /* ============================================================
        休憩機能
    ============================================================ */

    /** @test */
    public function 休憩入ボタンが正しく表示され_休憩処理後に休憩中になる()
    {
        $user = User::factory()->create([
            'status' => '出勤中',
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in' => now(),
            'clock_out' => null,
        ]);

        // 休憩入ボタンが表示される
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩入');

        // 休憩入処理
        $this->actingAs($user)->post('/attendance/updateStatus', [
            'action' => 'break_in',
        ]);

        $user->refresh();
        $this->assertEquals('休憩中', $user->status);

        // 再表示で休憩中が見える
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩中');
    }

    /** @test */
    public function 休憩は一日に何回でもできる()
    {
        $user = User::factory()->create([
            'status' => '出勤中',
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in' => now(),
            'clock_out' => null,
        ]);

        // 休憩入 → 休憩戻
        $this->actingAs($user)->post('/attendance/updateStatus', ['action' => 'break_in']);
        $this->actingAs($user)->post('/attendance/updateStatus', ['action' => 'break_out']);

        // 再度休憩入ボタンが表示される
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩入');
    }

    /** @test */
    public function 休憩戻ボタンが正しく機能し_処理後に出勤中になる()
    {
        $user = User::factory()->create([
            'status' => '出勤中',
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in' => now(),
            'clock_out' => null,
        ]);

        // 休憩入
        $this->actingAs($user)->post('/attendance/updateStatus', ['action' => 'break_in']);

        // 休憩戻
        $this->actingAs($user)->post('/attendance/updateStatus', ['action' => 'break_out']);

        $user->refresh();
        $this->assertEquals('出勤中', $user->status);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('出勤中');
    }

    /** @test */
    public function 休憩時刻が勤怠一覧画面で確認できる()
    {
        Carbon::setTestNow('2024-01-01 10:00:00');

        $user = User::factory()->create([
            'status' => '出勤中',
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2024-01-01',
            'clock_in' => '2024-01-01 09:00:00',
            'clock_out' => null,
        ]);

        // 休憩入
        $this->actingAs($user)->post('/attendance/updateStatus', ['action' => 'break_in']);

        Carbon::setTestNow('2024-01-01 10:30:00');

        // 休憩戻
        $this->actingAs($user)->post('/attendance/updateStatus', ['action' => 'break_out']);

        // 勤怠一覧画面
        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertSee('2024/1');
        $response->assertSee('01/01');
    }


    /* ============================================================
        退勤機能
    ============================================================ */

    /** @test */
    public function 退勤ボタンが正しく表示され_退勤処理後に退勤済になる()
    {
        $user = User::factory()->create([
            'status' => '出勤中',
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in' => now(),
        ]);

        // 退勤ボタンが表示される
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('退勤');

        // 退勤処理
        $this->actingAs($user)->post('/attendance/updateStatus', [
            'action' => 'clock_out',
        ]);

        $user->refresh();
        $this->assertEquals('退勤済', $user->status);

        // 再表示で退勤済が見える
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('退勤済');
    }

    /** @test */
    public function 退勤時刻が勤怠一覧画面で確認できる()
    {
        Carbon::setTestNow('2024-01-01 09:00:00');

        $user = User::factory()->create([
            'status' => '勤務外',
        ]);

        // 出勤
        $this->actingAs($user)->post('/attendance/updateStatus', ['action' => 'clock_in']);

        Carbon::setTestNow('2024-01-01 18:00:00');

        // 退勤
        $this->actingAs($user)->post('/attendance/updateStatus', ['action' => 'clock_out']);

        // 勤怠一覧画面
        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('18:00');
    }
}
