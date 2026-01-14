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

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminLoginController::class, 'index'])->name('login.index');
    // Route::post('/login', [AdminLoginController::class, 'login'])->name('login.login');
    Route::get('/logout', [AdminLoginController::class, 'logout'])->name('login.logout');

    Route::middleware(['auth:admin'])->group(function () {
        Route::get('/attendance/list', [AdminAttendanceController::class, 'show'])->name('attendance.list');
    });
});
