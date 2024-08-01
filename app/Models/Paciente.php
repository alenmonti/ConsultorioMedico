<?php

namespace App\Models;

use App\Models\Scopes\Own;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Paciente extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'telefono',
        'dni',
        'obra_social',
        'afiliado',
        'fecha_nacimiento',
        'direccion',
        'documento',
        'medico_id'
    ];

    public function historiasClinicas()
    {
        return $this->hasMany(HistoriaClinica::class)->orderBy('fecha', 'desc');
    }

    public function turnos()
    {
        return $this->hasMany(Turno::class);
    }

    public function medico()
    {
        return $this->belongsTo(User::class, 'medico_id', 'id');
    }

    protected static function booted()
    {
        static::addGlobalScope(Own::class);
    }

    public static function selectOptions()
    {
        $pacientes = Paciente::select('id', 'nombre', 'apellido', 'dni')->get();
        $options = [];
        foreach ($pacientes as $paciente) {
            $options[$paciente->id] = $paciente->nombre.' '.$paciente->apellido.', '.$paciente->dni;
        }
        return $options;
    }

    public function getDocumentoLinkAttribute()
    {
        return $this->documento ? asset('storage/'.$this->documento) : null;
    }
}
