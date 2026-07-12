<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Índice único funcional (MySQL 8.0.13+): las expresiones se evalúan a NULL
        // para turnos cancelados o para sobre_turnos, y MySQL no exige unicidad
        // entre NULLs, así que sólo se impide duplicar un mismo slot
        // (medico_id, fecha, hora) entre turnos activos de tipo 'turno'. Los
        // sobre_turnos quedan excluidos a propósito: el sistema permite dar más
        // de un sobre_turno (u overbookear sobre un turno normal) en el mismo slot.
        DB::statement(
            "CREATE UNIQUE INDEX turnos_medico_fecha_hora_activo_unique ON turnos (
                (CASE WHEN estado <> 'cancelado' AND tipo = 'turno' THEN medico_id END),
                (CASE WHEN estado <> 'cancelado' AND tipo = 'turno' THEN fecha END),
                (CASE WHEN estado <> 'cancelado' AND tipo = 'turno' THEN hora END)
            )"
        );
    }

    public function down(): void
    {
        DB::statement('DROP INDEX turnos_medico_fecha_hora_activo_unique ON turnos');
    }
};
