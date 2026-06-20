<?php

use App\Http\Controllers\Module\Setting\PermissionController;
use App\Http\Controllers\Module\Setting\RoleControlller;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'tenant', 'ajax'])->group(function () {
    // Role
    Route::prefix('role')->middleware('permission:role.manage')->group(function () {
        Route::get('/', [RoleControlller::class, 'index'])->name('setting.role');
        Route::get('/add', [RoleControlller::class, 'create'])->name('role.add');
        Route::post('/store', [RoleControlller::class, 'store'])->name('role.store');
        Route::post('/data', [RoleControlller::class, 'data'])->name('role.data');
        Route::get('/manage-permission/{id}', [RoleControlller::class, 'managePermission'])->name('role.manage-permission');
        Route::post('/manage-permission', [RoleControlller::class, 'storePermission'])->name('role.store-permission');
    });

    // Permission
    Route::prefix('permission')->middleware('permission:permission.manage')->group(function () {
        Route::get('/', [PermissionController::class, 'index'])->name('setting.permission');
        Route::get('/add', [PermissionController::class, 'create'])->name('permission.add');
        Route::post('/data', [PermissionController::class, 'data'])->name('permission.data');
        Route::post('/store', [PermissionController::class, 'store'])->name('permission.store');
        Route::get('/edit/{id}', [PermissionController::class, 'edit'])->name('permission.edit');
        Route::post('/update/{id}', [PermissionController::class, 'update'])->name('permission.update');
        Route::get('/destroy/{id}', [PermissionController::class, 'destroy'])->name('permission.destroy');
    });

    // User
});
