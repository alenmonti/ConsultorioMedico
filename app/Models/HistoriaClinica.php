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
        'fecha',

        'antecedentes',
        'toxicos',//
        'quirurgicos',//
        'alergias',//
        'vacunacion',//
        'medicacion',//

        'motivo',
        'examen_fisico',
        'evolucion',
        'diagnostico',
        'estudios',
        'tratamiento',
        'imagenes', //
        
        'resultados',
    ];

    protected $casts = [
        'imagenes' => 'array',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }
}
