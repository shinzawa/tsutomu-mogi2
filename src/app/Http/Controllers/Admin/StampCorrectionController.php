<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CorrectionRequestAttendance;
use Illuminate\Http\Request;

class StampCorrectionController extends Controller
{
    public function index()
    {
        return view('/admin/stamp_correction/index');
    }

    public function approve(Request $request, $attendance_correct_request_id)
    {
        // $id はAttendanceのprime index
        $user = $request->user;
        if ($attendance_correct_request_id > 0) {
            $correction_request_attendance = CorrectionRequestAttendance::with('breaks')->findOrFail($attendance_correct_request_id);
        } else {
            $correction_request_attendance = null;
            $date = $request->date;
        }

        return view('/admin/stamp_correction/show', compact('correction_request_attendance', 'user'));
    }
}
