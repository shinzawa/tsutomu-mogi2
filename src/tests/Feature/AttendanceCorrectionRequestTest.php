<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\CorrectionRequestAttendance;
use App\Models\CorrectionRequestBreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceCorrectionRequestTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $admin;
    private $attendance;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->user->email_verified_at = now();
        $this->user->save();
        $this->admin = Admin::factory()->create();

        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => '2026-01-27',
            'clock_in' => '2026-01-27 09:00:00',
            'clock_out' => '2026-01-27 18:00:00',
        ]);
    }

    /** @test */
    public function 出勤時間が退勤時間より後ならエラーになる()
    {
        $response = $this->actingAs($this->user)
            ->post(route('attendance.update', ['id' => $this->attendance->id]), [
                'clock_in' => '19:00',
                'clock_out' => '18:00',
                'note' => 'test',
            ]);
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['clock_in']);
        $this->assertEquals(
            '出勤時間が不適切な値です',
            session('errors')->first('clock_in')
        );
    }

    /** @test */
    public function 休憩開始が退勤時間より後ならエラーになる()
    {
        $response = $this->actingAs($this->user)
            ->post(route('attendance.update', $this->attendance->id), [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'break_start' => ['19:00'],
                'break_end' => ['19:30'],
                'note' => 'test',
            ]);

        $response->assertSessionHasErrors(['break_start.0']);
        $this->assertEquals(
            '休憩時間が不適切な値です',
            session('errors')->first('break_start.0')
        );
    }

    /** @test */
    public function 休憩終了が退勤時間より後ならエラーになる()
    {
        $response = $this->actingAs($this->user)
            ->post(route('attendance.update', $this->attendance->id), [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'break_start' => ['17:00'],
                'break_end' => ['19:00'],
                'note' => 'test',
            ]);

        $response->assertSessionHasErrors(['break_end.0']);
        $this->assertEquals(
            '休憩時間もしくは退勤時間が不適切な値です',
            session('errors')->first('break_end.0')
        );
    }

    /** @test */
    public function 備考欄が未入力ならエラーになる()
    {
        $response = $this->actingAs($this->user)
            ->post(route('attendance.update', $this->attendance->id), [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'note' => '',
            ]);

        $response->assertSessionHasErrors(['note']);
        $this->assertEquals(
            '備考を記入してください',
            session('errors')->first('note')
        );
    }

    /** @test */
    public function 修正申請が作成される()
    {
        $response = $this->actingAs($this->user)
            ->post(route('attendance.update', ['id' => $this->attendance->id]), [
                'clock_in' => '08:00',
                'clock_out' => '17:00',
                'break_start' => ['12:00'],
                'break_end' => ['12:30'],
                'note' => '修正理由',
            ]);
        // dd($response->headers->get('Location'));
        // dd($response->status(), $response->getContent(), session('errors'));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('correction_request_attendances', [
            'attendances_id' => $this->attendance->id,
            'user_id' => $this->user->id,
            'requested_clock_in' => '2026-01-27 08:00:00',
            'requested_clock_out' => '2026-01-27 17:00:00',
            'reason' => '修正理由',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('correction_request_breakes', [
            'start' => '2026-01-27 12:00:00',
            'end' => '2026-01-27 12:30:00',
        ]);
    }

    /** @test */
    public function 承認待ち一覧に自分の申請が表示される()
    {
        $request = CorrectionRequestAttendance::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('correction.index'));

        $response->assertSee($request->id);
    }

    /** @test */
    public function 承認済み一覧に管理者が承認した申請が表示される()
    {
        $request = CorrectionRequestAttendance::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('correction.index'));

        $response->assertSee($request->id);
    }

    /** @test */
    public function 申請の詳細画面に遷移できる()
    {
        $request = CorrectionRequestAttendance::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('correction.show', $request->id));

        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
    }
}
