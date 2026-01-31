<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function show(Request $request)
    {
        // 現在の年月
        $current = Carbon::now();
        $year = $current->year;
        $month = $current->month;
        $day = $current->day;

        // 前日・翌日
        $prev = $current->copy()->subDay();
        $next = $current->copy()->addDay();

        // 表示したい日付（指定がなければ今日）
        $date = $request->input('date', now()->toDateString());

        // 全スタッフを取得しつつ、その日の勤怠を eager load
        $users = User::with(['attendances' => function ($query) use ($date) {
            $query->where('work_date', $date);
        }])->orderBy('name')->get();

        return view('admin.attendance.index', [
            'date' => $date,
            'users' => $users,
            'year' => $year,
            'month' => $month,
            'day' => $day,
            'prevYear' => $prev->year,
            'prevMonth' => $prev->month,
            'prevDay' => $prev->day,
            'nextYear' => $next->year,
            'nextMonth' => $next->month,
            'nextDay' => $next->day,
        ]);
    }

    public function detail(Request $request, $id)
    {
        // $id はAttendanceのprime index
        $user = $request->user;
        if ($id > 0) {
            $attendance = Attendance::with('breaks')->findOrFail($id);
        } else {
            $attendance = null;
            $date = $request->date;
        }

        return view('admin.attendance.show', compact('attendance', 'user'));
    }

    public function staffIndex()
    {
        $users = User::all();
        return view('/admin/staff/index', compact('users'));
    }
    //スタッフ別勤怠一覧画面（管理者）
    public function index($id)
    {
        // 現在の年月
        $current = Carbon::now();
        $year = $current->year;
        $month = $current->month;

        // 前月・翌月
        $prev = $current->copy()->subMonth();
        $next = $current->copy()->addMonth();

        // 選択したスタッフのユーザーID
        $userId = $id;
        $user = User::find($id);
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

        return view('admin.staff.attendance.index', [
            'user' => $user,
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
}
