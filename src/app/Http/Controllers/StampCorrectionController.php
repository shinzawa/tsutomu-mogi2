<?php

namespace App\Http\Controllers;

use App\Models\CorrectionRequestAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StampCorrectionController extends Controller
{
    public function show($id)
    {
        $request = CorrectionRequestAttendance::with('breaks', 'attendance')->findOrFail($id);

        // 一般ユーザーは自分の申請のみ閲覧可能
        if (!Auth::user()->is_admin && $request->user_id !== Auth::id()) {
            abort(403);
        }

        return view('/stamp_correction/index', compact('request'));
    }

    public function index($id)
    {
        $correctionRequest = CorrectionRequestAttendance::with('breaks', 'attendance')->findOrFail($id);

        // 一般ユーザーは自分の申請のみ閲覧可能
        if ($correctionRequest->user_id !== Auth::id()) {
            abort(403);
        }

        $workDate = $correctionRequest->attendance->work_date;

        return view('/stamp_correction/index', compact('request','workDate'));
    }
}
