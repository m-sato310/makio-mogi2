<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use Illuminate\Support\Facades\Route;

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
    Route::get('/attendance', [AttendanceController::class, 'showAttendanceForm'])->name('attendance.form');
    Route::post('/attendance/start', [AttendanceController::class, 'clockIn'])->name('attendance.start');
    Route::post('/attendance/finish', [AttendanceController::class, 'clockOut'])->name('attendance.finish');
    Route::post('attendance/break/start', [AttendanceController::class, 'breakStart'])->name('attendance.break_start');
    Route::post('attendance/break/end', [AttendanceController::class, 'breakEnd'])->name('attendance.break_end');
});