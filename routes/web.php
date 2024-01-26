<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Module\Transaction\MeetingAttendanceController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Auth::routes();

Route::middleware('auth')->get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::middleware('auth')->get('/redirect', [DashboardController::class, 'redirect'])->name('redirect');
Route::middleware(['auth', 'ajax'])->get('/home', [DashboardController::class, 'dashboard'])->name('dashboard.index');

Route::get('/form-attendance/{trx_number}', [MeetingAttendanceController::class, 'formAttendance'])->name('meeting-attendance.form-attendance');
