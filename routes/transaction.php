<?php

use App\Http\Controllers\Module\Transaction\MeetingAttendanceController;
use Illuminate\Support\Facades\Route;

Route::post('/meeting-attendance/store', [MeetingAttendanceController::class, 'store'])->name('meeting-attendance.store');
Route::middleware(['auth', 'ajax'])->group(function(){
    // Meeting Attendance
    Route::prefix('meeting-attendance')->group(function(){
        Route::get('/', [MeetingAttendanceController::class, 'index'])->name('transaction.meeting-attendance');
        Route::get('/add', [MeetingAttendanceController::class, 'create'])->name('meeting-attendance.add');
        Route::post('/data', [MeetingAttendanceController::class, 'data'])->name('meeting-attendance.data');
        Route::get('/attendance-list/{meeting_id}', [MeetingAttendanceController::class, 'attendanceList'])->name('meeting-attendance.attendance-list');
        Route::get('/data/{id}', [MeetingAttendanceController::class, 'edit'])->name('meeting-attendance.edit');
    });
});
?>
