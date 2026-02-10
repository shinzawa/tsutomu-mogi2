<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use App\Models\CorrectionRequestAttendance;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Http\Requests\CorrectionRequestAttendanceRequest;

class AttendanceController extends Controller
{
    public function list(Request $request)
    {
        $year = $request->query('year', Carbon::now()->year);
        $month = $request->query('month', Carbon::now()->month);
        $day = $request->query('day', Carbon::now()->day);

        // Carbon インスタンスを指定年月で作成
        $current = Carbon::create($year, $month, $day);

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

    public function show(Request $request, $id)
    {
        // $id は Attendance の primary key
        if ($id > 0) {
            $attendance = Attendance::with('breaks')->findOrFail($id);
            $user = $attendance->user; // Attendance から User を取得
        } else {
            $attendance = null;
            $date = $request->date;

            // 新規作成時は user_id をクエリから取得
            $userId = $request->query('user');
            $user = User::findOrFail($userId);
        }

        return view('admin.attendance.show', compact('attendance', 'user'));
    }

    public function staffIndex()
    {
        $users = User::all();
        return view('/admin/staff/index', compact('users'));
    }
    //スタッフ別勤怠一覧画面（管理者）
    public function index(Request $request, $id)
    {
        $year = $request->query('year', Carbon::now()->year);
        $month = $request->query('month', Carbon::now()->month);

        // Carbon インスタンスを指定年月で作成
        $current = Carbon::create($year, $month, 1);

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
        $attendanceMap = $attendances->mapWithKeys(function ($attendance) {
            return [
                $attendance->work_date->format('Y-m-d') => $attendance
            ];
        });

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

    

    public function update(CorrectionRequestAttendanceRequest $request, $id)
    {
        $attendance = Attendance::with('breaks')->findOrFail($id);

        // ▼ 1. 承認待ちの修正申告があるかチェック
        $hasPendingRequest = CorrectionRequestAttendance::where('attendance_id', $attendance->id)
            ->where('status', 'pending')
            ->exists();

        if ($hasPendingRequest) {
            return back()->with('error', '承認待ちのため修正はできません。');
        }

        $workDate = Carbon::parse($attendance->work_date)->toDateString();
        // ▼ 2. 管理者による直接修正（申請は発行しない）
        $attendance->clock_in  = $request->clock_in ?  $workDate . ' ' . $request->clock_in : null;
        $attendance->clock_out = $request->clock_out ? $workDate . ' ' . $request->clock_out : null;
        $attendance->note      = $request->note;
        $attendance->save();

        // ▼ 3. 休憩時間の更新処理
        $breakStarts = $request->break_start ?? [];
        $breakEnds   = $request->break_end ?? [];

        // 既存 break を一旦削除して再登録する方式（最もシンプルで整合性が高い）
        $attendance->breaks()->delete();

        foreach ($breakStarts as $i => $start) {
            $end = $breakEnds[$i] ?? null;

            // 空欄行はスキップ
            if (empty($start) && empty($end)) {
                continue;
            }

            $attendance->breaks()->create([
                'start' => $workDate . ' ' . $start,
                'end'   => $workDate . ' ' . $end,
            ]);
        }

        return redirect()
            ->route('admin.attendance.show', $attendance->id)
            ->with('success', '勤怠情報を修正しました。');
    }

    public function exportCsv(Request $request, User $user)
    {
        $year = $request->year;
        $month = $request->month;

        // 指定月の勤怠データ取得
        $attendances = Attendance::where('user_id', $user->id)
            ->whereYear('work_date', $year)
            ->whereMonth('work_date', $month)
            ->with('breaks')
            ->orderBy('work_date')
            ->get();

        $fileName = "{$user->name}_{$year}-{$month}_attendance.csv";

        $response = new StreamedResponse(function () use ($attendances) {

            $stream = fopen('php://output', 'w');

            // ヘッダー行
            fputcsv($stream, [
                '日付',
                '出勤',
                '退勤',
                '休憩合計(分)',
                '勤務時間(分)',
                '休憩詳細'
            ]);

            foreach ($attendances as $a) {

                // 休憩詳細（例: "10:00-10:15 / 15:00-15:10"）
                $breakDetails = $a->breaks->map(function ($b) {
                    $start = Carbon::parse($b->start)->format('H:i');
                    $end   = Carbon::parse($b->end)->format('H:i');
                    return "{$start}-{$end}";
                })->implode(' / ');

                fputcsv($stream, [
                    $a->work_date->format('Y-m-d'),
                    optional($a->clock_in)->format('H:i'),
                    optional($a->clock_out)->format('H:i'),
                    $a->total_break_minutes,
                    $a->work_minutes,
                    $breakDetails,
                ]);
            }

            fclose($stream);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', "attachment; filename={$fileName}");

        return $response;
    }
}
