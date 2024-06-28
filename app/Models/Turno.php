<?php

namespace App\Models;

use App\Enums\EstadosTurno;
use App\Models\Scopes\Own;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Turno extends Model
{
    use HasFactory;

    protected $fillable = [
        'paciente_id',
        'medico_id',
        'fecha',
        'estado',
        'notas'
    ];

    protected function casts(): array
    {
        return [
            'estado' => EstadosTurno::class,
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

    protected static function booted()
    {
        static::addGlobalScope(Own::class);
    }
}
