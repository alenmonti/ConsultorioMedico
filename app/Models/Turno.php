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
        'notas'
    ];

    protected function casts(): array
    {
        return [
            'estado' => EstadosTurno::class,
            'tipo' => TipoTurno::class,
            'hora' => TimeCast::class
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
