<?php

use App\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\StampCorrectionController;
use App\Http\Controllers\Admin\StampCorrectionController as AdminStampCorrectionController;
use App\Http\Controllers\CorrectionRequestAttendanceController;
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
    Route::get('/attendance/list', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'detail'])->name('attendance.detail');
    Route::get('/stamp_correction_request/list', [StampCorrectionController::class, 'index']);
    Route::post('/attendance/detail/{id}', [AttendanceController::class, 'update'])->name('attendance.update');
    Route::get('/attendance/????', [AttendanceController::class, '']);
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminLoginController::class, 'index'])->name('login.index');
    Route::get('/logout', [AdminLoginController::class, 'logout'])->name('login.logout');

    Route::middleware(['auth:admin'])->group(function () {
        Route::get('/attendance/list', [AdminAttendanceController::class, 'show'])->name('attendance.list');
        Route::get('/attendance/{id}', [AdminAttendanceController::class, 'detail'])->name('attendance.detail');
        Route::get('/staff/list', [AdminAttendanceController::class, 'staffIndex'])->name('staff.list');
        Route::get('/attendance/staff/{id}', [AdminAttendanceController::class, 'index'])->name('staff.attendance.index');
        Route::get('/stamp_correction_request/list', [AdminStampCorrectionController::class, 'index'])->name('attendance.list');
        Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminStampCorrectionController::class, 'approve'])->name('attendance.list');
    });
});
