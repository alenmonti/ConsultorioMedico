<?php

namespace App\Models;

use App\Casts\TimeCast;
use App\Enums\Dias;
use App\Models\Scopes\Own;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    use HasFactory;

    protected $fillable = [
        'medico_id',
        'anio',
        'mes',
        'dia',
        'desde',
        'hasta',
        'intervalo',
        'activo_sistema',
        'activo_portal',
    ];

    public function medico()
    {
        return $this->belongsTo(User::class, 'medico_id', 'id');
    }

    public function casts(): array
    {
        return [
            'desde' => TimeCast::class,
            'hasta' => TimeCast::class,
            'intervalo' => TimeCast::class,
            'dia' => Dias::class,
            'activo_sistema' => 'boolean',
            'activo_portal' => 'boolean',
        ];
    }

    public static function booted()
    {
        static::creating(function ($horario) {
            $horario->medico_id = $horario->medico_id ?? auth()->user()->medico_id;
            $horario->anio = $horario->anio ?? now()->year;
            $horario->mes = $horario->mes ?? now()->month;
        });

        static::addGlobalScope(Own::class);
    }
}
