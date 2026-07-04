<?php

namespace App\Observers;

use App\Enums\Roles;
use App\Models\Horario;
use App\Models\Practica;
use App\Models\User;

class UserObserver
{
    public function creating(User $user)
    {
        $user->rol = $user->rol ?? Roles::Medico;
    }

    public function created(User $user)
    {
        if ($user->rol == Roles::Medico) {
            // Set medico_id to himself
            $user->medico_id = $user->id;
            $user->save();

            // Create default practica
            $user->practicas()->create(['nombre' => 'Consulta', 'costo' => 35000, 'duracion_min' => 20]);

            // Create default schedule
            $user->horarios()->createMany([
                ['dia' => 'lunes', 'desde' => '09:00', 'hasta' => '18:00', 'intervalo' => '00:20', 'anio' => now()->year, 'mes' => now()->month],
                ['dia' => 'martes', 'desde' => '09:00', 'hasta' => '18:00', 'intervalo' => '00:20', 'anio' => now()->year, 'mes' => now()->month],
                ['dia' => 'miercoles', 'desde' => '09:00', 'hasta' => '18:00', 'intervalo' => '00:20', 'anio' => now()->year, 'mes' => now()->month],
                ['dia' => 'jueves', 'desde' => '09:00', 'hasta' => '18:00', 'intervalo' => '00:20', 'anio' => now()->year, 'mes' => now()->month],
                ['dia' => 'viernes', 'desde' => '09:00', 'hasta' => '18:00', 'intervalo' => '00:20', 'anio' => now()->year, 'mes' => now()->month],
            ]);
        }
    }
}
