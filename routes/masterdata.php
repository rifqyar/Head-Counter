<?php

use App\Http\Controllers\Module\MasterData\ClientController;
use App\Http\Controllers\Module\MasterData\MeetingScheduleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'ajax'])->group(function(){
    // Client
    Route::prefix('client')->group(function(){
        Route::get('/', [ClientController::class, 'index'])->name('masterdata.client')->middleware('can:Client');
        Route::get('/add', [ClientController::class, 'create'])->name('client.add')->middleware('can:client.add');
        Route::post('/store', [ClientController::class, 'store'])->name('client.store')->middleware('can:client.add');
        Route::post('/data', [ClientController::class, 'data'])->name('client.data');
        Route::get('/edit/{id}', [ClientController::class, 'edit'])->name('client.edit')->middleware('can:client.edit');
    });

    // Meeting Schedule
    Route::prefix('meeting-schedule')->group(function(){
        Route::get('/', [MeetingScheduleController::class, 'index'])->name('masterdata.meeting-schedule')->middleware('can:Meeting Schedule');
        Route::get('/add', [MeetingScheduleController::class, 'create'])->name('meeting-schedule.add')->middleware('can:meeting.add');
        Route::post('/store', [MeetingScheduleController::class, 'store'])->name('meeting-schedule.store')->middleware('can:meeting.add');
        Route::post('/data', [MeetingScheduleController::class, 'data'])->name('meeting-schedule.data');
        Route::get('/edit/{id}', [MeetingScheduleController::class, 'edit'])->name('meeting-schedule.edit')->middleware('can:meeting.edit');
        Route::get('/generate-qr/{id}', [MeetingScheduleController::class, 'generateQrCode'])->name('meeting-schedule.generate-qr');
    });
});

?>
