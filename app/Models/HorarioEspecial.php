<?php

namespace App\Models;

use App\Enums\TipoHorarioEspecial;
use App\Models\Scopes\Own;
use Illuminate\Database\Eloquent\Model;

class HorarioEspecial extends Model
{
    protected $table = 'horarios_especiales';

    protected $fillable = [
        'medico_id',
        'fecha',
        'tipo',
        'todo_el_dia',
        'desde',
        'hasta',
        'motivo',
        'activo_sistema',
        'activo_portal',
    ];

    protected $casts = [
        'fecha' => 'date',
        'tipo' => TipoHorarioEspecial::class,
        'todo_el_dia' => 'boolean',
        'activo_sistema' => 'boolean',
        'activo_portal' => 'boolean',
    ];

    public function medico()
    {
        return $this->belongsTo(User::class, 'medico_id', 'id');
    }

    public static function booted(): void
    {
        static::creating(function ($especial) {
            $especial->medico_id = $especial->medico_id ?? auth()->user()->medico_id;
        });

        static::addGlobalScope(Own::class);
    }
}
