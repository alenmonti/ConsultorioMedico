<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoriaClinica extends Model
{
    use HasFactory;

    public $table = 'historias_clinicas';

    protected $fillable = [
        'paciente_id',
        'motivo',
        'diagnostico',
        'estudios',
        'tratamiento',
        'medicamentos',
        'examen_fisico',
        'resultados',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

}
