<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminLoginController extends Controller
{
    public function index()
    {
        return view('/admin/login');
    }
    //ログイン処理
    public function login(LoginRequest $request)
    {
        $credentials = $request->only(['email', 'password']);
        Log::info('LOGIN');
        //ユーザー情報が見つかったらログイン
        if (Auth::guard('admin')->attempt($credentials)) {
            //ログイン後に表示するページにリダイレクト
            Log::info('LOGIN admin');
            return redirect()->route('admin.attendance.list')->with([
                'login_msg' => 'ログインしました。',
            ]);
        }

        //ログインできなかったときに元のページに戻る
        return back()->withErrors([
            'login' => ['ログインに失敗しました'],
        ]);
    }

    //ログアウト処理
    public function logout(Request $request)
    {
        Log::info('LOGOUT');
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        //ログインページにリダイレクト
        return redirect()->route('admin.login.index')->with([
            'logout_msg' => 'ログアウトしました',
        ]);
    }
}
