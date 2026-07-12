<?php

namespace App\Models;

use App\Casts\TimeCast;
use App\Enums\EstadosTurno;
use App\Enums\TipoTurno;
use App\Models\Scopes\orderByDHU;
use App\Models\Scopes\Own;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Turno extends Model
{
    use HasFactory;

    protected $fillable = [
        'paciente_id',
        'medico_id',
        'practica_id',
        'fecha',
        'hora',
        'estado',
        'tipo',
        'notas',
        'origen',
        'aviso_asignacion_enviado_at',
        'senia_informada_at',
        'senia_pagada_at',
        'recordatorio_enviado_at',
        'turno_token',
    ];

    protected function casts(): array
    {
        return [
            'estado' => EstadosTurno::class,
            'tipo' => TipoTurno::class,
            'hora' => TimeCast::class,
            'aviso_asignacion_enviado_at' => 'datetime',
            'senia_informada_at' => 'datetime',
            'senia_pagada_at' => 'datetime',
            'recordatorio_enviado_at' => 'datetime',
        ];
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function medico()
    {
        return $this->belongsTo(User::class, 'medico_id', 'id');
    }

    public function practica()
    {
        return $this->belongsTo(Practica::class);
    }

    protected static function booted()
    {
        static::addGlobalScope(Own::class);
        static::addGlobalScope(orderByDHU::class);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('fecha', Carbon::today());
    }

}
