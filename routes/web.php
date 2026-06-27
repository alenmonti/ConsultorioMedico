<?php

use App\Http\Controllers\Portal\PortalTurnosController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('linkstorage', function () {
    Artisan::call('storage:link');
    return 'Storage linked successfully.';
});
Route::get('migrate', function () {
    Artisan::call('migrate');
    return 'Database migrated successfully.';
});
Route::get('seed', function () {
    Artisan::call('db:seed');
    return 'Database seeded successfully.';
});
Route::get('health', function () {
    return response()->json(['status' => 'ok'], 200);
});

Route::prefix('portal-turnos')->group(function () {
    Route::get('/', [PortalTurnosController::class, 'index'])->name('portal.turnos');
    Route::get('/medicos', [PortalTurnosController::class, 'medicos'])->name('portal.medicos');
    Route::get('/semana', [PortalTurnosController::class, 'semana'])->name('portal.semana');
    Route::get('/horarios', [PortalTurnosController::class, 'horarios'])->name('portal.horarios');
    Route::post('/reservar', [PortalTurnosController::class, 'reservar'])->name('portal.reservar');
});
