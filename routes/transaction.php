<?php

use App\Http\Controllers\Module\Transaction\MeetingAttendanceController;
use Illuminate\Support\Facades\Route;

Route::post('/meeting-attendance/store', [MeetingAttendanceController::class, 'store'])->name('meeting-attendance.store');
Route::middleware(['auth', 'ajax'])->group(function(){
    // Meeting Attendance
    Route::prefix('meeting-attendance')->group(function(){
        Route::get('/', [MeetingAttendanceController::class, 'index'])->name('masterdata.meeting-attendance');
        Route::get('/add', [MeetingAttendanceController::class, 'create'])->name('meeting-attendance.add');
        // Route::post('/store', [MeetingAttendanceController::class, 'store'])->name('meeting-attendance.store');
        Route::post('/data', [MeetingAttendanceController::class, 'data'])->name('meeting-attendance.data');
        Route::get('/data/{id}', [MeetingAttendanceController::class, 'edit'])->name('meeting-attendance.edit');
    });
});
?>
