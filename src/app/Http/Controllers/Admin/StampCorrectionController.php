<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CorrectionRequestAttendance;
use App\Models\User;
use Illuminate\Http\Request;

class StampCorrectionController extends Controller
{
    public function index()
    {
        $pending = CorrectionRequestAttendance::with('breaks', 'attendance')
            ->where('status', 'pending')
            ->get();

        $approved = CorrectionRequestAttendance::with('breaks', 'attendance')
            ->where('status', 'approved')
            ->get();

        $users = User::all();

        return view('/admin/stamp_correction/index', compact('pending', 'approved', 'users'));
    }

    public function show(Request $request, $attendance_correct_request_id)
    {
        // $id はAttendanceのprime index

        $correction = CorrectionRequestAttendance::with('breaks')->findOrFail($attendance_correct_request_id);
        
        $user = $correction->attendance->user;

        return view('admin.stamp_correction.show', compact('correction', 'user'));
    }

    public function approve($id)
    {
        // 申請データを取得
        $correction = CorrectionRequestAttendance::with(['attendance', 'breaks'])
            ->findOrFail($id);

        // ステータス変更
        $correction->status = 'approved';
        $correction->reviewed_at = now();
        $correction->save();

        // 対象の勤怠データを取得
        $attendance = $correction->attendance;

        // 勤怠データを申請内容で更新
        $attendance->clock_in  = $correction->requested_clock_in;
        $attendance->clock_out = $correction->requested_clock_out;
        $attendance->note      = $correction->reasen;

        // 休憩合計を再計算（breaks がある前提）
        $totalBreak = $correction->breaks->sum('break_minutes');
        $attendance->total_break_minutes = $totalBreak;

        // DB に反映
        $attendance->save();
        $attendance->breaks()->delete();

        foreach ($correction->breaks as $correctionBreak) {
            $attendance->breaks()->create([
                'start' => $correctionBreak->start,
                'end' => $correctionBreak->end,
            ]);
        }

        return redirect()
            ->route('admin.stamp_correction.show', $id)
            ->with('success', '申請を承認し、勤怠データを更新しました');
    }
}
