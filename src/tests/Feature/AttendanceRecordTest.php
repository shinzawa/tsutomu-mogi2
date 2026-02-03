<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceRecordTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    /** @test */
    public function 現在の日時情報がUIと同じ形式で出力されている()
    {
        Carbon::setTestNow(Carbon::create(2024, 1, 15, 9, 30)); // 固定日時

        $user = User::factory()->create([
            'status' => '勤務外',
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);

        // Blade と同じフォーマット
        $expectedDate = Carbon::now()->locale('ja')->isoFormat('YYYY年M月D日(ddd)');
        $expectedTime = Carbon::now()->format('H:i');

        $response->assertSee($expectedDate);
        $response->assertSee($expectedTime);
    }

    /** @test */
    public function 勤務外の場合_ステータスが正しく表示される()
    {
        $user = User::factory()->create([
            'status' => '勤務外',
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }

    /** @test */
    public function 出勤中の場合_ステータスが正しく表示される()
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

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    /** @test */
    public function 休憩中の場合_ステータスが正しく表示される()
    {
        $user = User::factory()->create([
            'status' => '休憩中',
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in' => now(),
            'clock_out' => null,
        ]);

        // 休憩中の break レコード
        $attendance->breaks()->create([
            'start' => now()->subMinutes(10),
            'end' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    /** @test */
    public function 退勤済の場合_ステータスが正しく表示される()
    {
        $user = User::factory()->create([
            'status' => '退勤済',
        ]);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in' => now()->subHours(8),
            'clock_out' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }
}
