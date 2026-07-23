<?php

use App\Http\Controllers\Portal\PortalTurnosController;
use App\Http\Controllers\TurnoImprimirController;
use App\Http\Controllers\TurnoPublicController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

// Route::get('linkstorage', function () {
//     Artisan::call('storage:link');
//     return 'Storage linked successfully.';
// });
Route::get('migrate', function () {
    $output = new \Symfony\Component\Console\Output\BufferedOutput();
    Artisan::call('migrate', ['--force' => true], $output);

    return response('<pre>'.$output->fetch().'</pre>');
});
// Route::get('seed', function () {
//     $output = new \Symfony\Component\Console\Output\BufferedOutput();
//     Artisan::call('db:seed', ['--force' => true], $output);
//     return response('<pre>' . $output->fetch() . '</pre>');
// });
Route::get('health', function () {
    return response()->json(['status' => 'ok'], 200);
});

Route::get('optimize', function () {
    $output = new \Symfony\Component\Console\Output\BufferedOutput();
    Artisan::call('optimize', [], $output);
    Artisan::call('view:cache', [], $output);

    return response('<pre>'.$output->fetch().'</pre>');
});

Route::get('optimize-clear', function () {
    $output = new \Symfony\Component\Console\Output\BufferedOutput();
    Artisan::call('optimize:clear', [], $output);

    return response('<pre>'.$output->fetch().'</pre>');
});

// Route::get('migrate-storage', function () {
//     $dryRun   = request()->boolean('dry_run', true);
//     $moveFiles = request()->boolean('move_files', false);

//     $limit = (int) request()->input('limit', 0);

//     $output = new \Symfony\Component\Console\Output\BufferedOutput();
//     Artisan::call('storage:migrate-paths', [
//         '--dry-run'    => $dryRun,
//         '--move-files' => $moveFiles,
//         '--limit'      => $limit,
//     ], $output);

//     return response('<pre>' . $output->fetch() . '</pre>');
// });

Route::prefix('turno')->group(function () {
    Route::get('/confirmar/{turno}', [TurnoPublicController::class, 'confirmar'])->name('turno.confirmar');
    Route::get('/cancelar/{turno}', [TurnoPublicController::class, 'cancelar'])->name('turno.cancelar');
});

Route::get('/turnos/imprimir', TurnoImprimirController::class)
    ->middleware('auth')
    ->name('turnos.imprimir');

// Rutas para disparar tareas cron vía wget (hostings que no permiten
// ejecutar comandos de consola directamente, ej. Donweb/Ferozo).
// Reutiliza REGISTRATION_CODE como token de acceso.
Route::prefix('cron/{token}')->group(function () {
    Route::get('/turnos-resumen-manana', function (string $token) {
        abort_unless(hash_equals((string) config('app.registration_code'), $token), 403);

        $output = new \Symfony\Component\Console\Output\BufferedOutput();
        Artisan::call('turnos:enviar-resumen-manana', [], $output);

        return response('<pre>'.$output->fetch().'</pre>');
    });

    Route::get('/queue-work', function (string $token) {
        abort_unless(hash_equals((string) config('app.registration_code'), $token), 403);

        $output = new \Symfony\Component\Console\Output\BufferedOutput();
        Artisan::call('queue:work', [
            '--stop-when-empty' => true,
            '--max-time' => 25,
        ], $output);

        return response('<pre>'.$output->fetch().'</pre>');
    });
});

Route::prefix('portal-turnos')->group(function () {
    Route::get('/', [PortalTurnosController::class, 'index'])->name('portal.turnos');
    Route::get('/medicos', [PortalTurnosController::class, 'medicos'])->name('portal.medicos');
    Route::get('/semana', [PortalTurnosController::class, 'semana'])->name('portal.semana');
    Route::get('/disponibilidad', [PortalTurnosController::class, 'disponibilidad'])->name('portal.disponibilidad');
    Route::get('/horarios', [PortalTurnosController::class, 'horarios'])->name('portal.horarios');
    Route::post('/reservar', [PortalTurnosController::class, 'reservar'])->name('portal.reservar');
});
