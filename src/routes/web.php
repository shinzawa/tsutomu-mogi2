<?php

use App\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Requests\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'show']);
});

// Route::middleware(['auth:web'])->group(function () {
//     Route::get('/dashboard', function () {
//         return view('dashboard');
//     });
// });

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('login');

    Route::middleware(['auth:admin'])->group(function () {
        Route::get('/attendance/list', [AdminAttendanceController::class, 'show']);
    });
});

Route::post('/admin/logout', function () {
    Auth::guard('admin')->logout();
    return redirect('/admin/login');
});

Route::post('/admin/attendance/list', function () {
    Log::info('POST HIT admin/attendance/list');
    abort(405);
});

Route::post('/admin/login', [AuthenticatedSessionController::class, 'store']);
