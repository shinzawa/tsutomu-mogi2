<?php

namespace Tests\Feature\Attendance;

use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceIndexTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::create(2025, 2, 15));

        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();

        // 自分の今月の勤怠（2件）
        Attendance::factory()->for($this->user)->create([
            'work_date' => '2025-02-01',
        ]);
        Attendance::factory()->for($this->user)->create([
            'work_date' => '2025-02-02',
        ]);

        // 自分の翌月
        Attendance::factory()->for($this->user)->create([
            'work_date' => '2025-03-05',
        ]);

        // 他ユーザーの今月
        Attendance::factory()->for($this->otherUser)->create([
            'work_date' => '2025-02-06',
        ]);

        // 自分の固定データ（重複しない日付にする）
        Attendance::factory()->for($this->user)->create([
            'work_date' => '2025-02-04',
            'clock_in' => '2025-02-04 09:00:00',
            'clock_out' => '2025-02-04 18:00:00',
        ]);
    }

    /** @test */
    public function 勤怠一覧に自分の勤怠情報のみが表示される()
    {
        $response = $this->actingAs($this->user)
            ->get(route('attendance.index'))
            ->assertStatus(200);

        // 自分の勤怠は表示される
        $this->user->attendances->each(function ($attendance) use ($response) {
            if ($attendance->clock_in) {
                $response->assertSee(Carbon::parse($attendance->clock_in)->format('H:i'));
            }
        });

        // 他ユーザーの勤怠は表示されない
        $this->otherUser->attendances->each(function ($attendance) use ($response) {
            if ($attendance->clock_in) {
                $response->assertDontSee(Carbon::parse($attendance->clock_in)->format('H:i'));
            }
        });
    }

    /** @test */
    public function 初期表示で現在の月の勤怠一覧が表示される()
    {
        $response = $this->actingAs($this->user)
            ->get(route('attendance.index'));

        // 画面上に現在の月が表示されている（表示形式はプロジェクトに合わせて調整）
        $response->assertSee(Carbon::now()->format('Y-m'));

        // 今月の勤怠は表示される
        $this->user->attendances()
            ->whereBetween('work_date', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->get()
            ->each(function ($attendance) use ($response) {
                $response->assertSee($attendance->work_date->format('Y-m-d'));
            });

        // 前月・翌月の勤怠は表示されない想定なら、ここでチェック
        $this->user->attendances()
            ->where('work_date', '<', Carbon::now()->startOfMonth())
            ->orWhere('work_date', '>', Carbon::now()->endOfMonth())
            ->get()
            ->each(function ($attendance) use ($response) {
                $response->assertDontSee($attendance->work_date->format('Y-m-d'));
            });
    }

    /** @test */
    public function 前月ボタン押下で前月の勤怠一覧が表示される()
    {
        // コントローラ側で ?month=YYYY-MM などのクエリで月を切り替える想定
        $targetMonth = Carbon::now()->subMonth()->format('Y-m');

        $response = $this->actingAs($this->user)
            ->get(route('attendance.index', ['month' => $targetMonth]));

        $response->assertSee($targetMonth);

        // 前月の勤怠は表示される
        $this->user->attendances()
            ->whereBetween('work_date', [
                Carbon::now()->subMonth()->startOfMonth(),
                Carbon::now()->subMonth()->endOfMonth(),
            ])
            ->get()
            ->each(function ($attendance) use ($response) {
                $response->assertSee($attendance->work_date->format('Y-m-d'));
            });

        // 今月・翌月の勤怠は表示されない想定
        $this->user->attendances()
            ->whereNotBetween('work_date', [
                Carbon::now()->subMonth()->startOfMonth(),
                Carbon::now()->subMonth()->endOfMonth(),
            ])
            ->get()
            ->each(function ($attendance) use ($response) {
                $response->assertDontSee($attendance->work_date->format('Y-m-d'));
            });
    }

    /** @test */
    public function 翌月ボタン押下で翌月の勤怠一覧が表示される()
    {
        $targetMonth = Carbon::now()->addMonth()->format('Y-m');

        $response = $this->actingAs($this->user)
            ->get(route('attendance.index', ['month' => $targetMonth]));

        $response->assertSee($targetMonth);

        // 翌月の勤怠は表示される
        $this->user->attendances()
            ->whereBetween('work_date', [
                Carbon::now()->addMonth()->startOfMonth(),
                Carbon::now()->addMonth()->endOfMonth(),
            ])
            ->get()
            ->each(function ($attendance) use ($response) {
                $response->assertSee($attendance->work_date->format('Y-m-d'));
            });

        // 今月・前月の勤怠は表示されない想定
        $this->user->attendances()
            ->whereNotBetween('work_date', [
                Carbon::now()->addMonth()->startOfMonth(),
                Carbon::now()->addMonth()->endOfMonth(),
            ])
            ->get()
            ->each(function ($attendance) use ($response) {
                $response->assertDontSee($attendance->work_date->format('Y-m-d'));
            });
    }

    /** @test */
    public function 詳細ボタン押下でその日の勤怠詳細画面に遷移する()
    {
        $attendance = $this->user->attendances()->first();

        $response = $this->actingAs($this->user)
            ->get(route('attendances.show', $attendance->id));

        $response->assertStatus(200);
        $response->assertViewIs('attendances.show'); // ビュー名はプロジェクトに合わせて変更
        $response->assertSee($attendance->work_date->format('Y-m-d'));
    }
}
