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

Auth::routes();

Route::middleware('auth')->get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::middleware('auth')->get('/redirect', [DashboardController::class, 'redirect'])->name('redirect');
Route::middleware(['auth', 'ajax'])->get('/home', [DashboardController::class, 'dashboard'])->name('dashboard.index');

Route::middleware('throttle:attendance')->get('/form-attendance', [MeetingAttendanceController::class, 'formAttendance'])->name('meeting-attendance.form-attendance');
Route::middleware('throttle:attendance')->group(function () {
    Route::get('/attendance/meeting/{token}', [PublicMeetingAttendanceController::class, 'show'])->name('attendance.meeting.show');
    Route::post('/attendance/meeting/{token}/register', [PublicMeetingAttendanceController::class, 'register'])->name('attendance.meeting.register');
});
Route::middleware('auth')->get('test', function () {
    phpinfo();
});

Route::middleware(['auth', 'tenant'])->group(function () {
    Route::resource('hotels', HotelController::class);
    Route::resource('meeting-rooms', MeetingRoomController::class);
    Route::resource('clients', ClientDomainController::class);
    Route::resource('bookings', BookingController::class);
    Route::resource('meetings', MeetingEventController::class);
    Route::post('meetings/{meeting}/transition', [MeetingEventController::class, 'transition'])->name('meetings.transition');
    Route::post('meetings/{meeting}/qr/generate', [MeetingQRCodeController::class, 'generate'])->middleware('permission:meeting.qr.manage')->name('meetings.qr.generate');
    Route::post('meetings/{meeting}/qr/regenerate', [MeetingQRCodeController::class, 'regenerate'])->middleware('permission:meeting.qr.manage')->name('meetings.qr.regenerate');
    Route::post('meetings/{meeting}/qr/revoke', [MeetingQRCodeController::class, 'revoke'])->middleware('permission:meeting.qr.manage')->name('meetings.qr.revoke');
    Route::get('meetings/{meeting}/qr/download', [MeetingQRCodeController::class, 'download'])->middleware('permission:meeting.qr.manage')->name('meetings.qr.download');
    Route::post('meetings/{meeting}/meal-sessions/generate', [MealSessionController::class, 'generate'])->middleware('permission:meal_session.manage')->name('meetings.meal-sessions.generate');
    Route::resource('participants', ParticipantController::class);
    Route::get('participants/{participant}/qr', [ParticipantQRCodeController::class, 'show'])->middleware('permission:participant.qr.manage')->name('participants.qr.show');
    Route::post('participants/{participant}/qr/generate', [ParticipantQRCodeController::class, 'generate'])->middleware('permission:participant.qr.manage')->name('participants.qr.generate');
    Route::post('participants/{participant}/qr/rotate', [ParticipantQRCodeController::class, 'rotate'])->middleware('permission:participant.qr.manage')->name('participants.qr.rotate');
    Route::post('participants/{participant}/qr/revoke', [ParticipantQRCodeController::class, 'revoke'])->middleware('permission:participant.qr.manage')->name('participants.qr.revoke');
    Route::resource('packages', MeetingPackageController::class);
    Route::resource('meal-sessions', MealSessionController::class)->except(['show', 'destroy'])->middleware('permission:meal_session.manage');
    Route::post('meal-sessions/{mealSession}/open', [MealSessionController::class, 'open'])->middleware('permission:meal_session.manage')->name('meal-sessions.open');
    Route::post('meal-sessions/{mealSession}/close', [MealSessionController::class, 'close'])->middleware('permission:meal_session.manage')->name('meal-sessions.close');
    Route::post('meal-sessions/{mealSession}/cancel', [MealSessionController::class, 'cancel'])->middleware('permission:meal_session.manage')->name('meal-sessions.cancel');
    Route::get('scanner', [ScannerController::class, 'page'])->middleware('permission:redemption.scan')->name('scanner.index');
    Route::get('redemptions', [RedemptionController::class, 'index'])->middleware('permission:redemption.view')->name('redemptions.index');
    Route::get('redemptions/{redemption}', [RedemptionController::class, 'show'])->middleware('permission:redemption.view')->name('redemptions.show');
    Route::post('redemptions/{redemption}/override', [RedemptionController::class, 'override'])->middleware('permission:redemption.override')->name('redemptions.override');
    Route::post('redemptions/{redemption}/reverse', [RedemptionController::class, 'reverse'])->middleware('permission:redemption.reverse')->name('redemptions.reverse');
    Route::get('tenant-switch', [TenantSwitchController::class, 'index'])->name('tenant-switch.index');
    Route::post('tenant-switch', [TenantSwitchController::class, 'switch'])->name('tenant-switch.switch');
    Route::delete('tenant-switch', [TenantSwitchController::class, 'reset'])->name('tenant-switch.reset');
});
