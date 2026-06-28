<?php

use App\Http\Controllers\Portal\PortalTurnosController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('linkstorage', function () {
    Artisan::call('storage:link');
    return 'Storage linked successfully.';
});
Route::get('migrate', function () {
    $output = new \Symfony\Component\Console\Output\BufferedOutput();
    Artisan::call('migrate', ['--force' => true], $output);
    return response('<pre>' . $output->fetch() . '</pre>');
});
Route::get('seed', function () {
    $output = new \Symfony\Component\Console\Output\BufferedOutput();
    Artisan::call('db:seed', ['--force' => true], $output);
    return response('<pre>' . $output->fetch() . '</pre>');
});
Route::get('health', function () {
    return response()->json(['status' => 'ok'], 200);
});

Route::get('migrate-storage', function () {
    $dryRun   = request()->boolean('dry_run', true);
    $moveFiles = request()->boolean('move_files', false);

    $limit = (int) request()->input('limit', 0);

    $output = new \Symfony\Component\Console\Output\BufferedOutput();
    Artisan::call('storage:migrate-paths', [
        '--dry-run'    => $dryRun,
        '--move-files' => $moveFiles,
        '--limit'      => $limit,
    ], $output);

    return response('<pre>' . $output->fetch() . '</pre>');
});

Route::prefix('portal-turnos')->group(function () {
    Route::get('/', [PortalTurnosController::class, 'index'])->name('portal.turnos');
    Route::get('/medicos', [PortalTurnosController::class, 'medicos'])->name('portal.medicos');
    Route::get('/semana', [PortalTurnosController::class, 'semana'])->name('portal.semana');
    Route::get('/horarios', [PortalTurnosController::class, 'horarios'])->name('portal.horarios');
    Route::post('/reservar', [PortalTurnosController::class, 'reservar'])->name('portal.reservar');
});
