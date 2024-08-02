<?php

use App\Http\Controllers\Module\MasterData\ClientController;
use App\Http\Controllers\Module\MasterData\MeetingScheduleController;
use Illuminate\Support\Facades\Route;

Route{
    // Client
    Route::prefix('client')->group(function(){
        Route::get('/', [ClientController::class, 'index'])->name('masterdata.client');
        Route::get('/add', [ClientController::class, 'create'])->name('client.add');
        Route::post('/store', [ClientController::class, 'store'])->name('client.store');
        Route::post('/data', [ClientController::class, 'data'])->name('client.data');
        Route::get('/edit/{id}', [ClientController::class, 'edit'])->name('client.edit');
        Route::get('/get-detail/{code}', [ClientController::class, 'getDetail']);
    });

    // Meeting Schedule
    Route::prefix('meeting-schedule')->group(function(){
        Route::get('/', [MeetingScheduleController::class, 'index'])->name('masterdata.meeting-schedule');
        Route::get('/add', [MeetingScheduleController::class, 'create'])->name('meeting-schedule.add');
        Route::post('/store', [MeetingScheduleController::class, 'store'])->name('meeting-schedule.store');
        Route::post('/data', [MeetingScheduleController::class, 'data'])->name('meeting-schedule.data');
        Route::get('/edit/{id}', [MeetingScheduleController::class, 'edit'])->name('meeting-schedule.edit');
        Route::get('/generate-qr/{id}', [MeetingScheduleController::class, 'generateQrCode'])->name('meeting-schedule.generate-qr');
        Route::get('/get-qr/{id}', [MeetingScheduleController::class, 'getQR'])->name('meeting-schedule.get-qr');
    });
});

?>
