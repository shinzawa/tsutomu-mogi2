<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
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

    public function detail($id)
    {
        $attendance = Attendance::with('breaks')->findOrFail($id);

        // ログインユーザー以外のデータを見れないようにする
        if ($attendance->user_id !== Auth::id()) {
            abort(403);
        }

        return view('attendance.show', compact('attendance'));
    }
}
