<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Requests\LoginRequest;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
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
                // 一般ユーザー
                Auth::guard('web')->logout();
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
            $guard = $request->input('guard', 'web');

            $credentials = $request->only('email', 'password');

            if ($guard === 'admin') {
                if (Auth::guard('admin')->attempt($credentials)) {
                    return Auth::guard('admin')->user();
                }
            }

            if ($guard === 'web') {
                if (Auth::guard('web')->attempt($credentials)) {
                    return Auth::guard('web')->user();
                }
            }

            return null;
        });
    }
}
