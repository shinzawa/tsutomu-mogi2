<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 勤怠詳細画面にて名前がログインユーザーの氏名になっている()
    {
        $user = User::factory()->create([
            'name' => '山田太郎',
        ]);

        $attendance = Attendance::factory()->for($user)->create([
            'id' => 1,
            'work_date' => '2024-01-10',
        ]);

        $response = $this->actingAs($user)
            ->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
    }

    /** @test */
    public function 勤怠詳細画面の日付が選択した日付になっている()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->for($user)->create([
            'id' => 1,
            'work_date' => '2024-01-10',
        ]);

        $response = $this->actingAs($user)
            ->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('2024年');
        $response->assertSee('1月10日');
    }

    /** @test */
    public function 出勤退勤時間がログインユーザーの打刻と一致している()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->for($user)->create([
            'id' => 1,
            'work_date'      => '2024-01-10',
            'clock_in'  => '2024-01-10 09:00',
            'clock_out' => '2024-01-10 18:00',
        ]);

        $response = $this->actingAs($user)
            ->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function 休憩時間がログインユーザーの打刻と一致している()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->for($user)->create([
            'id' => 1,
            'work_date' => '2024-01-10',
        ]);

        BreakTime::factory()->for($attendance)->create([
            'start' => '2024-01-10 12:00',
            'end'   => '2024-01-10 13:00',
        ]);

        $response = $this->actingAs($user)
            ->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }
}
