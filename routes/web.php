<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GelanggangConfigController;
use App\Http\Controllers\FileSystemController;
use App\Http\Controllers\Pm2Controller;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/api/status', [DashboardController::class, 'status'])->name('status.poll');
Route::get('/api/directories', [FileSystemController::class, 'listDirectories'])->name('fs.directories');

/*
|--------------------------------------------------------------------------
| Gelanggang Config CRUD
|--------------------------------------------------------------------------
*/
Route::post('/gelanggang', [GelanggangConfigController::class, 'store'])->name('gelanggang.store');
Route::put('/gelanggang/{gelanggangConfig}', [GelanggangConfigController::class, 'update'])->name('gelanggang.update');
Route::delete('/gelanggang/{gelanggangConfig}', [GelanggangConfigController::class, 'destroy'])->name('gelanggang.destroy');

/*
|--------------------------------------------------------------------------
| PM2 Process Control
|--------------------------------------------------------------------------
*/
Route::post('/pm2/{gelanggangConfig}/start', [Pm2Controller::class, 'startAll'])->name('pm2.startAll');
Route::post('/pm2/{gelanggangConfig}/stop', [Pm2Controller::class, 'stopAll'])->name('pm2.stopAll');
Route::post('/pm2/{gelanggangConfig}/toggle', [Pm2Controller::class, 'toggleProcess'])->name('pm2.toggle');
Route::post('/pm2/{gelanggangConfig}/delete', [Pm2Controller::class, 'deleteAll'])->name('pm2.deleteAll');
