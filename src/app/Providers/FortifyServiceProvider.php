<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Requests\LoginRequest;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Contracts\RegisterResponse;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\LogoutResponse;
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;

class FortifyServiceProvider extends ServiceProvider
{
    public function register()
    {
        // 登録後のリダイレクト
        $this->app->instance(RegisterResponse::class, new class implements RegisterResponse {
            public function toResponse($request)
            {
                return redirect('/attendance');
            }
        });

        // ログイン後のリダイレクト
        $this->app->instance(LoginResponse::class, new class implements LoginResponse {
            public function toResponse($request)
            {
                // 管理者ログイン後
                if (Auth::guard('admin')->check()) {
                    return redirect('/admin/attendance/list');
                }

                // 一般ユーザー
                return redirect('/attendance');
            }
        });

        // ログアウト後のリダイレクト
        $this->app->instance(LogoutResponse::class, new class implements LogoutResponse {
            public function toResponse($request)
            {
                Log::info("LOGOUT toResponse");
                // まず、どのガードでログインしていたかを判定
                $isAdmin = Auth::guard('admin')->check();

                // 先にログアウト処理
                if ($isAdmin) {
                    Log::info("LOGOUT admin");
                    Auth::guard('admin')->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return redirect('/admin/login');
                }

                // 一般ユーザー
                Auth::guard('web')->logout();
                Log::info("LOGOUT user");
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect('/login');
            }
        });
    }

    public function boot()
    {
        // Fortify の LoginRequest を差し替え
        $this->app->bind(FortifyLoginRequest::class, LoginRequest::class);

        // 新規登録
        Fortify::createUsersUsing(CreateNewUser::class);

        // 画面
        Fortify::registerView(fn() => view('auth.register'));
        Fortify::loginView(fn() => view('auth.login'));
        Fortify::verifyEmailView('auth.verify-email');

        // ログイン試行回数制限
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;
            return Limit::perMinute(10)->by($email . $request->ip());
        });

        /**
         * ----------------------------------------------------
         *  認証処理（管理者 + 一般ユーザー）
         * ----------------------------------------------------
         */
        Fortify::authenticateUsing(function ($request) {
            // 管理者ログイン
            if ($request->is('admin/login')) {
                $admin = \App\Models\Admin::where('email', $request->email)->first();

                if ($admin && Hash::check($request->password, $admin->password)) {
                    Auth::guard('admin')->login($admin);
                    return $admin;
                }

                return null; // 管理者認証失敗
            }

            // 一般ユーザー
            $user = \App\Models\User::where('email', $request->email)->first();

            if ($user && Hash::check($request->password, $user->password)) {
                return $user; // web ガードでログイン
            }

            return null;
        });
    }
}
