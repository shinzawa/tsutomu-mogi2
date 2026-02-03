<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\CorrectionRequestAttendance;
use App\Models\CorrectionRequestBreakTime;
use App\Models\User;
use App\Http\Requests\CorrectionRequestAttendanceRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function show()
    {
        $user = auth()->user();
        $today = now()->startOfDay();

        // 本日の出勤データを取得（例：出勤時間があるレコード）
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->first();

        $status = $user->status; // デフォルト
dd($attendance);
        if ($attendance) {
            if ($attendance->end_time === null) {
                // 退勤していない場合
                if ($attendance->break_start_time !== null && $attendance->break_end_time === null) {
                    $status = '休憩中';
                } elseif ($attendance->break_start_time !== null && $attendance->break_end_time !== null) {
                    $status = '出勤中';
                } else {
                    $status = '出勤中';
                }
            } else {
                $status = '退勤済';
            }
        }

        return view('attendance.record', compact('status', 'attendance'));
    }

    public function updateStatus(Request $request)
    {
        $user = auth()->user();
        $action = $request->input('action');
        $today = now()->toDateString();

        // 今日の勤怠レコード
        $attendance = Attendance::firstOrCreate(
            ['user_id' => $user->id, 'work_date' => $today]
        );

        switch ($action) {

            case 'clock_in':
                // 出勤 → レコード作成
                // 出勤は1回だけ
                if (!$attendance->clock_in) {
                    $attendance->clock_in = now();
                    $attendance->save();
                }
                $user->status = '出勤中';
                break;

            case 'break_in':
                // 休憩開始 → breaks に新規レコード
                $attendance->breaks()->create([
                    'start' => now(),
                ]);
                $user->status = '休憩中';
                break;

            case 'break_out':
                // 最後の break の break_out を更新
                $lastBreak = $attendance->breaks()->latest()->first();

                if ($lastBreak && !$lastBreak->break_out) {
                    $lastBreak->update([
                        'end' => now(),
                    ]);
                }

                $user->status = '出勤中';
                break;

            case 'clock_out':
                // 退勤は1回だけ
                if (!$attendance->clock_out) {
                    $attendance->clock_out = now();
                    $attendance->save();
                }
                $user->status = '退勤済';
                break;
        }

        $user->save();

        return back();
    }

    //勤怠一覧画面（一般ユーザー）
    public function index(Request $request)
    {
        // 現在の年月
        $current = Carbon::now();
        $year = $current->year;
        $month = $current->month;

        // 前月・翌月
        $prev = $current->copy()->subMonth();
        $next = $current->copy()->addMonth();

        // ログインユーザーID
        $userId = Auth::id();

        // 当月の全日付を生成
        $dates = [];
        $start = $current->copy()->startOfMonth();
        $end   = $current->copy()->endOfMonth();

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $dates[] = $date->copy();
        }

        // 当月の勤怠データを取得
        $attendances = Attendance::where('user_id', $userId)
            ->whereYear('work_date', $year)
            ->whereMonth('work_date', $month)
            ->with('breaks') // 休憩時間も取得
            ->orderBy('work_date')
            ->get();

        // 勤怠データを日付キーでまとめる
        $attendanceMap = $attendances->keyBy('work_date');

        return view('attendance.index', [
            'dates' => $dates,
            'attendanceMap' => $attendanceMap,
            'year' => $year,
            'month' => $month,
            'prevYear' => $prev->year,
            'prevMonth' => $prev->month,
            'nextYear' => $next->year,
            'nextMonth' => $next->month,
        ]);
    }

    public function detail(Request $request, $id)
    {
        // $id はAttendanceのprime index
        $userId = Auth::id();
        $user = User::find($userId);
        if ($id > 0) {
            $attendance = Attendance::with('breaks')->findOrFail($id);
            // ログインユーザー以外のデータを見れないようにする。
            // TODO: でもエラーを仕込んでよいのかでもエラーを仕込んでよいのか
            if ($attendance->user_id !== Auth::id()) {
                abort(403);
            }
        } else {
            $attendance = null;
            $date = $request->date;
        }

        return view('attendance.show', compact('attendance', 'user'));
    }

    public function update(CorrectionRequestAttendanceRequest $request, $id)
    {
        $attendance = Attendance::with('breaks')->findOrFail($id);

        // ログインユーザー以外は編集不可
        if ($attendance->user_id !== Auth::id()) {
            abort(403);
        }

        // ▼▼▼ 1. 修正申請（correction_request_attendances）を作成 ▼▼▼
        $correction = CorrectionRequestAttendance::create([
            'attendances_id'       => $attendance->id,
            'user_id'              => Auth::id(),
            'requested_clock_in'   => $attendance->work_date . ' ' . $request->clock_in,
            'requested_clock_out'  => $attendance->work_date . ' ' . $request->clock_out,
            'reason'               => $request->note,
            'status'               => 'pending',
        ]);

        // ▼▼▼ 2. 修正申請用の休憩（correction_request_breakes）を登録 ▼▼▼
        $startList = $request->break_start;   // 配列
        $endList   = $request->break_end;     // 配列

        if ($startList && $endList) {
            foreach ($startList as $i => $start) {

                $end = $endList[$i] ?? null;

                // 空行はスキップ
                if (empty($start) && empty($end)) {
                    continue;
                }
                // start または end が片方だけ → スキップ
                if (empty($start) || empty($end)) {
                    continue;
                }
                // 修正申請用の休憩を保存
                CorrectionRequestBreakTime::create([
                    'request_id' => $correction->id,
                    'start'      => $attendance->work_date . ' ' . $start,
                    'end'        => $attendance->work_date . ' ' . $end,
                ]);
            }
        }

        // ▼▼▼ 3. 完了メッセージ ▼▼▼
        return redirect()
            ->route('attendance.pendingDetail', $correction->id)
            ->with('success', '修正申請を送信しました(承認待ち)');
    }
}
