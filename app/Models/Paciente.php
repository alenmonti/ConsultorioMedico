<?php

namespace App\Models;

use App\Models\Scopes\Own;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'apellido',
        'email',
        'telefono',
        'dni',
        'afiliado',
        'fecha_nacimiento',
        'medico_id'
    ];

    public function historiasClinicas()
    {
        return $this->hasOne(HistoriaClinica::class);
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
}
