<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceIndexTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;
    protected $user1;
    protected $user2;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::create(2026, 02, 04));

        $this->admin = Admin::factory()->create();
        $this->user = User::factory()->create();
        $this->user1 = User::factory()->create();
        $this->user2 = User::factory()->create();

        // 自分の今月の勤怠（2件）
        Attendance::factory()->for($this->user)->create([
            'work_date' => '2026-02-01',
        ]);
        Attendance::factory()->for($this->user)->create([
            'work_date' => '2026-02-02',
        ]);

        // 自分の前月
        Attendance::factory()->for($this->user)->create([
            'work_date' => '2026-01-01',
        ]);
        Attendance::factory()->for($this->user)->create([
            'work_date' => '2026-01-02',
        ]);

        // 自分の翌月
        Attendance::factory()->for($this->user)->create([
            'work_date' => '2026-03-05',
            'clock_in' => '2026-03-05 09:00:00',
            'clock_out' => '2026-03-05 18:00:00',
        ]);

        // 自分の固定データ（重複しない日付にする）
        Attendance::factory()->for($this->user1)->create([
            'work_date' => '2026-02-04',
            'clock_in' => '2026-02-04 09:00:00',
            'clock_out' => '2026-02-04 18:00:00',
        ]);
        Attendance::factory()->for($this->user2)->create([
            'work_date' => '2026-02-04',
            'clock_in' => '2026-02-04 10:00:00',
            'clock_out' => '2026-02-04 19:00:00',
        ]);
    }

    /** @test */
    public function 管理者はその日の全ユーザーの勤怠情報を確認できる()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.attendance.index'));
        
        $response->assertStatus(200);

        // ユーザー1
        $response->assertSee($this->user1->name);
        $response->assertSee('09:00');
        $response->assertSee('18:00');

        // ユーザー2
        $response->assertSee($this->user2->name);
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }

    /** @test */
    public function 一覧画面に今日の日付が表示される()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.attendance.index'));

        $response->assertStatus(200);
        $response->assertSee(today()->format('Y/n/j'));
    }

    /** @test */
    public function 前日ボタン押下で前日の勤怠情報が表示される()
    {
        $yesterday = today()->subDay()->toDateString();
        [$year, $month, $day] = explode('-', $yesterday);

        Attendance::factory()->for($this->user)->create([
            'work_date' => $yesterday,
            'clock_in'  => '2024-02-03 09:30',
            'clock_out' => '2024-02-03 17:30',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.attendance.index', ['year' => $year, 'month' => $month, 'day' => $day, 'date' => $yesterday]));

        $yesterday = today()->subDay()->format('Y/n/j');
        $response->assertStatus(200);
        $response->assertSee($yesterday);
        $response->assertSee('09:30');
        $response->assertSee('17:30');
    }

    /** @test */
    public function 翌日ボタン押下で翌日の勤怠情報が表示される()
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();

        $tomorrow = today()->addDay()->toDateString();
        [$year, $month, $day] = explode('-', $tomorrow);

        Attendance::factory()->for($user)->create([
            'work_date' => $tomorrow,
            'clock_in'  => '2024-02-05 08:00',
            'clock_out' => '2024-02-05 16:00',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.attendance.index', ['year' => $year, 'month' => $month, 'day' => $day, 'date' => $tomorrow]));

        $tomorrow = today()->addDay()->format('Y/n/j');
        $response->assertStatus(200);
        $response->assertSee($tomorrow);
        $response->assertSee('08:00');
        $response->assertSee('16:00');
    }
}
