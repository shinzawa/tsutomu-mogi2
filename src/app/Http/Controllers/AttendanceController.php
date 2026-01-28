<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\CorrectionRequestAttendance;
use App\Models\CorrectionRequestBreakTime;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function show()
    {
        return view('/attendance/record');
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
        if ($id>0) {
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

    public function update(Request $request, $id)
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
            'reason'               => $request->reason,
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
            ->route('attendance.show', $attendance->id)
            ->with('success', '修正申請を送信しました');
    }
}
