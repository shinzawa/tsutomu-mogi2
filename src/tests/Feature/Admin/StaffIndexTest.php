<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffIndexTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;
    protected $user1;
    protected $user2;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::create(2026, 02, 05));

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

    /** @test */
    public function 管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.staff.list'));

        $response->assertStatus(200);

        // ユーザー1
        $response->assertSee($this->user1->name);
        $response->assertSee($this->user1->email);
        // ユーザー2
        $response->assertSee($this->user2->name);
        $response->assertSee($this->user->email);
    }

    /** @test */
    public function ユーザーの勤怠情報が正しく表示される()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.staff.attendance.index', ['id' => $this->user->id]));

        $response->assertStatus(200);
        $response->assertSee(today()->isoFormat('MM/DD'));
        $response->assertSee(today()->isoFormat('(ddd)'));
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function 前月の情報が表示される()
    {
        $yesterday = today()->subMonth()->toDateString();
        [$year, $month] = explode('-', $yesterday);

        Attendance::factory()->for($this->user)->create([
            'work_date' => $yesterday,
            'clock_in'  => '2024-02-03 09:30',
            'clock_out' => '2024-02-03 17:30',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.staff.attendance.index', ['id' => $this->user->id,'year' => $year, 'month' => $month]));

        $yesterday = today()->subMonth()->isoFormat('MM/DD');
        $dayWeek = today()->subMonth()->isoFormat('(ddd)');
        $response->assertStatus(200);
        $response->assertSee($yesterday);
        $response->assertSee($dayWeek);
        $response->assertSee('09:30');
        $response->assertSee('17:30');
    }

    /** @test */
    public function 翌月の情報が表示される()
    {
         $tomorrow = today()->addMonth()->toDateString();
        [$year, $month] = explode('-', $tomorrow);

        Attendance::factory()->for($this->user)->create([
            'work_date' => $tomorrow,
            'clock_in'  => '2024-02-05 08:00',
            'clock_out' => '2024-02-05 16:00',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.staff.attendance.index', ['id' => $this->user->id,'year' => $year, 'month' => $month]));

        $tomorrow = today()->addMonth()->isoFormat('MM/DD');
        $dayWeek = today()->addMonth()->isoFormat('(ddd)');
        $response->assertStatus(200);
        $response->assertSee($tomorrow);
        $response->assertSee($dayWeek);
        $response->assertSee('08:00');
        $response->assertSee('16:00');
    }


    /** @test */
    public function その日の勤怠詳細画面に遷移する()
    {
        $attendance = $this->user->attendances()->first();

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.attendance.show', $attendance->id));

        $response->assertStatus(200);
        $response->assertViewIs('admin.attendance.show'); // ビュー名はプロジェクトに合わせて変更
        $response->assertSee($attendance->work_date->format('n月j日'));
    }
}
