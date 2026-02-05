<?php

namespace App\Http\Controllers;

use App\Models\CorrectionRequestAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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

    public function index()
    {
        $user = Auth::user();
        $pending = CorrectionRequestAttendance::with('breaks', 'attendance')
            ->where('user_id', $user->id)->where('status', 'pending')
            ->get();

        $approved = CorrectionRequestAttendance::with('breaks', 'attendance')
            ->where('user_id', $user->id)->where('status', 'approved')
            ->get();

        return view('/stamp_correction/index', compact('pending', 'approved', 'user'));
    }

    public function pendingDetail($id)
    {
        Log::info('pendigDetail start');
        $user = Auth::user();
        $correction = CorrectionRequestAttendance::with(['attendance', 'breaks'])
            ->findOrFail($id);
        Log::info('pendigDetail before end');
        return view('stamp_correction.pending_detail', compact('correction', 'user'));
    }
}
