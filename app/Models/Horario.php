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
        'dia',
        'desde',
        'hasta',
        'intervalo'
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
            'dia' => Dias::class
        ];
    }

    public function getHorariosArray($fecha)
    {
        $horarios = $this->where('dia', $fecha->format('N'))->get();
        $horariosArray = [];
        foreach ($horarios as $horario) {
            $desde = $horario->desde;
            $hasta = $horario->hasta;
            $intervalo = $horario->intervalo;
            $hora = $desde;
            while ($hora <= $hasta) {
                $horariosArray[] = $hora;
                $hora = $hora->add($intervalo);
            }
        }
        return $horariosArray;
    
    }

    public static function booted()
    {
        static::creating(function ($horario) {
            $horario->medico_id = $horario->medico_id ?? auth()->user()->medico_id;
        });

        static::addGlobalScope(Own::class);
    }
}
