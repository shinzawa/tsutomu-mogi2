<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CorrectionRequestAttendance;
use Illuminate\Support\Facades\Auth;

class CorrectionRequestAttendanceController extends Controller
{
    /**
     * 修正申請一覧（承認待ち・承認済み）
     */
    public function index()
    {
        $user = Auth::user();

        // 管理者は全件、一般ユーザーは自分の申請のみ
        if ($user->is_admin ?? false) {
            $pending = CorrectionRequestAttendance::where('status', 'pending')->get();
            $approved = CorrectionRequestAttendance::where('status', 'approved')->get();
        } else {
            $pending = CorrectionRequestAttendance::where('user_id', $user->id)
                ->where('status', 'pending')
                ->get();

            $approved = CorrectionRequestAttendance::where('user_id', $user->id)
                ->where('status', 'approved')
                ->get();
        }

        return view('corrections.index', compact('pending', 'approved'));
    }

    /**
     * 修正申請の詳細画面
     */
    public function show($id)
    {
        $request = CorrectionRequestAttendance::with('breaks', 'attendance')->findOrFail($id);

        // 一般ユーザーは自分の申請のみ閲覧可能
        if (!Auth::user()->is_admin && $request->user_id !== Auth::id()) {
            abort(403);
        }

        return view('corrections.show', compact('request'));
    }

    /**
     * 管理者：承認処理
     */
    public function approve($id)
    {
        $request = CorrectionRequestAttendance::findOrFail($id);

        if (!Auth::user()->is_admin) {
            abort(403);
        }

        $request->update([
            'status' => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return redirect()->route('correction.index')
            ->with('success', '修正申請を承認しました');
    }

    /**
     * 管理者：却下処理
     */
    public function reject($id)
    {
        $request = CorrectionRequestAttendance::findOrFail($id);

        if (!Auth::user()->is_admin) {
            abort(403);
        }

        $request->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return redirect()->route('correction.index')
            ->with('success', '修正申請を却下しました');
    }

    public function pendingDetail($id)
    {
        $correction = CorrectionRequestAttendance::with(['attendance', 'breaks'])
            ->findOrFail($id);

        // 一般ユーザーは自分の申請のみ閲覧可能
        if ($correction->user_id !== auth()->id()) {
            abort(403);
        }

        return view('stamp_correction_request.pending_detail', compact('correction'));
    }
}
