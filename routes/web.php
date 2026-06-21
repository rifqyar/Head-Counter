<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\ClientDomainController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\MealSessionController;
use App\Http\Controllers\MeetingEventController;
use App\Http\Controllers\MeetingPackageController;
use App\Http\Controllers\MeetingQRCodeController;
use App\Http\Controllers\MeetingRoomController;
use App\Http\Controllers\Module\Transaction\MeetingAttendanceController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\ParticipantQRCodeController;
use App\Http\Controllers\PublicMeetingAttendanceController;
use App\Http\Controllers\RedemptionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportExportController;
use App\Http\Controllers\ScannerController;
use App\Http\Controllers\TenantSwitchController;
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

Auth::routes(['register' => false]);

Route::middleware('auth')->get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::middleware('auth')->get('/redirect', [DashboardController::class, 'redirect'])->name('redirect');
Route::middleware(['auth', 'tenant', 'ajax'])->get('/home', [DashboardController::class, 'dashboard'])->name('dashboard.index');

Route::middleware('throttle:attendance')->get('/form-attendance', [MeetingAttendanceController::class, 'formAttendance'])->name('meeting-attendance.form-attendance');
Route::middleware('throttle:attendance')->group(function () {
    Route::get('/attendance/meeting/{token}', [PublicMeetingAttendanceController::class, 'show'])->name('attendance.meeting.show');
    Route::post('/attendance/meeting/{token}/register', [PublicMeetingAttendanceController::class, 'register'])->name('attendance.meeting.register');
});
Route::middleware(['auth', 'tenant'])->group(function () {
    Route::resource('hotels', HotelController::class)->middleware('permission:hotel.manage');
    Route::resource('meeting-rooms', MeetingRoomController::class)->middleware('permission:meeting_room.view|meeting_room.manage');
    Route::resource('clients', ClientDomainController::class)->middleware('permission:client.view|client.manage');
    Route::resource('bookings', BookingController::class)->middleware('permission:booking.view|booking.create|booking.update|booking.cancel');
    Route::resource('meetings', MeetingEventController::class)->middleware('permission:meeting.view|meeting.create|meeting.update|meeting.cancel');
    Route::post('meetings/{meeting}/transition', [MeetingEventController::class, 'transition'])->middleware('permission:meeting.start|meeting.complete|meeting.cancel')->name('meetings.transition');
    Route::post('meetings/{meeting}/qr/generate', [MeetingQRCodeController::class, 'generate'])->middleware(['permission:meeting.qr.manage', 'throttle:sensitive-admin'])->name('meetings.qr.generate');
    Route::post('meetings/{meeting}/qr/regenerate', [MeetingQRCodeController::class, 'regenerate'])->middleware(['permission:meeting.qr.manage', 'throttle:sensitive-admin'])->name('meetings.qr.regenerate');
    Route::post('meetings/{meeting}/qr/revoke', [MeetingQRCodeController::class, 'revoke'])->middleware(['permission:meeting.qr.manage', 'throttle:sensitive-admin'])->name('meetings.qr.revoke');
    Route::get('meetings/{meeting}/qr/download', [MeetingQRCodeController::class, 'download'])->middleware('permission:meeting.qr.manage')->name('meetings.qr.download');
    Route::post('meetings/{meeting}/meal-sessions/generate', [MealSessionController::class, 'generate'])->middleware('permission:meal_session.manage')->name('meetings.meal-sessions.generate');
    Route::resource('participants', ParticipantController::class)->middleware('permission:participant.view|participant.register|participant.update|participant.block');
    Route::get('participants/{participant}/qr', [ParticipantQRCodeController::class, 'show'])->middleware('permission:participant.qr.manage')->name('participants.qr.show');
    Route::post('participants/{participant}/qr/generate', [ParticipantQRCodeController::class, 'generate'])->middleware(['permission:participant.qr.manage', 'throttle:sensitive-admin'])->name('participants.qr.generate');
    Route::post('participants/{participant}/qr/rotate', [ParticipantQRCodeController::class, 'rotate'])->middleware(['permission:participant.qr.manage', 'throttle:sensitive-admin'])->name('participants.qr.rotate');
    Route::post('participants/{participant}/qr/revoke', [ParticipantQRCodeController::class, 'revoke'])->middleware(['permission:participant.qr.manage', 'throttle:sensitive-admin'])->name('participants.qr.revoke');
    Route::resource('packages', MeetingPackageController::class)->middleware('permission:meal_package.view|meal_package.manage');
    Route::resource('meal-sessions', MealSessionController::class)->except(['show', 'destroy'])->middleware('permission:meal_session.manage');
    Route::post('meal-sessions/{mealSession}/open', [MealSessionController::class, 'open'])->middleware('permission:meal_session.manage')->name('meal-sessions.open');
    Route::post('meal-sessions/{mealSession}/close', [MealSessionController::class, 'close'])->middleware('permission:meal_session.manage')->name('meal-sessions.close');
    Route::post('meal-sessions/{mealSession}/cancel', [MealSessionController::class, 'cancel'])->middleware('permission:meal_session.manage')->name('meal-sessions.cancel');
    Route::get('scanner', [ScannerController::class, 'page'])->middleware('permission:redemption.scan')->name('scanner.index');
    Route::get('redemptions', [RedemptionController::class, 'index'])->middleware('permission:redemption.view')->name('redemptions.index');
    Route::get('redemptions/{redemption}', [RedemptionController::class, 'show'])->middleware('permission:redemption.view')->name('redemptions.show');
    Route::post('redemptions/{redemption}/override', [RedemptionController::class, 'override'])->middleware(['permission:redemption.override', 'throttle:sensitive-admin'])->name('redemptions.override');
    Route::post('redemptions/{redemption}/reverse', [RedemptionController::class, 'reverse'])->middleware(['permission:redemption.reverse', 'throttle:sensitive-admin'])->name('redemptions.reverse');
    Route::get('audit-logs', [\App\Http\Controllers\AuditLogController::class, 'index'])->middleware('permission:audit.view')->name('audit-logs.index');
    Route::get('audit-logs/{auditLog}', [\App\Http\Controllers\AuditLogController::class, 'show'])->middleware('permission:audit.view')->name('audit-logs.show');
    Route::resource('users', \App\Http\Controllers\UserManagementController::class)->except(['destroy'])->middleware('permission:user.manage');
    Route::post('users/{user}/activate', [\App\Http\Controllers\UserManagementController::class, 'activate'])->middleware(['permission:user.manage', 'throttle:sensitive-admin'])->name('users.activate');
    Route::post('users/{user}/deactivate', [\App\Http\Controllers\UserManagementController::class, 'deactivate'])->middleware(['permission:user.manage', 'throttle:sensitive-admin'])->name('users.deactivate');
    Route::post('users/{user}/roles', [\App\Http\Controllers\UserManagementController::class, 'syncRoles'])->middleware(['permission:user.manage', 'throttle:sensitive-admin'])->name('users.roles.sync');
    Route::post('users/{user}/tokens', [\App\Http\Controllers\UserTokenController::class, 'store'])->middleware(['permission:user.manage', 'throttle:sensitive-admin'])->name('users.tokens.store');
    Route::delete('users/{user}/tokens/{token}', [\App\Http\Controllers\UserTokenController::class, 'destroy'])->middleware(['permission:user.manage', 'throttle:sensitive-admin'])->name('users.tokens.destroy');
    Route::delete('users/{user}/tokens', [\App\Http\Controllers\UserTokenController::class, 'destroyAll'])->middleware(['permission:user.manage', 'throttle:sensitive-admin'])->name('users.tokens.destroy-all');
    Route::get('tenant-switch', [TenantSwitchController::class, 'index'])->middleware('role:SUPER_ADMIN|Super Admin')->name('tenant-switch.index');
    Route::post('tenant-switch', [TenantSwitchController::class, 'switch'])->middleware(['role:SUPER_ADMIN|Super Admin', 'throttle:sensitive-admin'])->name('tenant-switch.switch');
    Route::delete('tenant-switch', [TenantSwitchController::class, 'reset'])->middleware(['role:SUPER_ADMIN|Super Admin', 'throttle:sensitive-admin'])->name('tenant-switch.reset');
    Route::get('reports', [ReportController::class, 'index'])->middleware('permission:report.view')->name('reports.index');
    Route::get('reports/exports', [ReportExportController::class, 'index'])->middleware('permission:report.export')->name('reports.exports.index');
    Route::get('reports/exports/{export}/download', [ReportExportController::class, 'download'])->middleware('permission:report.export')->name('reports.exports.download');
    Route::get('reports/{report}', [ReportController::class, 'show'])->middleware('permission:report.view')->name('reports.show');
    Route::post('reports/{report}/export', [ReportExportController::class, 'store'])->middleware(['permission:report.export', 'throttle:sensitive-admin'])->name('reports.export');
});
