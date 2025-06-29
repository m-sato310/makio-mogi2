<?php

use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminCorrectionRequestController;
use App\Http\Controllers\AdminStaffController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CorrectionRequestController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
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

Route::post('/register', [RegisterController::class, 'register'])->name('register.post');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');

Route::middleware(['auth'])->group(function () {
    Route::get('/verify-email', [RegisterController::class, 'showVerifyEmail'])->name('verification.notice');
    Route::post('/email/verification-notification', [RegisterController::class, 'sendVerificationEmail'])->name('verification.send');
    Route::get('/email/verify/{id}/{hash}', [RegisterController::class, 'verifyEmail'])->name('verification.verify');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'showAttendanceForm'])->name('attendance.form');
    Route::post('/attendance/start', [AttendanceController::class, 'clockIn'])->name('attendance.start');
    Route::post('/attendance/finish', [AttendanceController::class, 'clockOut'])->name('attendance.finish');
    Route::post('/attendance/break/start', [AttendanceController::class, 'breakStart'])->name('attendance.break_start');
    Route::post('/attendance/break/end', [AttendanceController::class, 'breakEnd'])->name('attendance.break_end');
    Route::get('/attendance/list', [AttendanceController::class, 'listMyAttendances'])->name('attendance.list');
    Route::get('/attendance/{id}', [AttendanceController::class, 'showAttendanceDetail'])->name('attendance.detail');
    Route::post('/attendance/{id}/correction', [CorrectionRequestController::class, 'applyCorrectionRequest'])->name('attendance.correction');
    Route::get('/stamp_correction_request/list', [CorrectionRequestController::class, 'listApplications'])->name('correction_request.list');
});

Route::get('/admin/login', function (Request $request) {
    return app(AuthenticatedSessionController::class)->create($request);
})->name('admin.login');
Route::post('/admin/login', [LoginController::class, 'adminLogin'])->name('admin.login.post');

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'listAllAttendances'])->name('admin.attendance.list');
    Route::post('/admin/attendance/{id}', [AdminAttendanceController::class, 'updateStaffAttendance'])->name('admin.attendance.update');
    Route::get('/admin/staff/list', [AdminStaffController::class, 'listStaffs'])->name('admin.staff.list');
    Route::get('/admin/attendance/staff/{id}', [AdminAttendanceController::class, 'listStaffAttendances'])->name('admin.attendance.staff');
    Route::get('/admin/attendance/staff/{id}/csv', [AdminAttendanceController::class, 'exportStaffAttendanceCsv'])->name('admin.attendance.staff.csv');
    Route::get('/stamp_correction_request/approve/{attendance_correct_request}', [AdminCorrectionRequestController::class, 'showApproveRequestForm'])->name('admin.correction_request.approve_form');
    Route::post('/stamp_correction_request/approve/{attendance_correct_request}', [AdminCorrectionRequestController::class, 'approveRequest'])->name('admin.correction_request.approve');
});