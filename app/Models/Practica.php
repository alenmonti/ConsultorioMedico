<?php

namespace App\Models;

use App\Models\Scopes\Own;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Practica extends Model
{
    use HasFactory;

    protected $fillable = [
        'medico_id',
        'nombre',
        'descripcion',
        'costo',
        'codigo_osde',
        'tipo',
        'duracion_min',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(Own::class);
    }

    protected function displayName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->tipo
                ? "[{$this->tipo}] {$this->nombre}"
                : $this->nombre
        );
    }

    public static function selectOptions(): array
    {
        return static::query()
            ->orderBy('tipo')
            ->orderBy('nombre')
            ->get()
            ->mapWithKeys(fn ($p) => [$p->id => $p->display_name])
            ->all();
    }

    public function medico()
    {
        return $this->belongsTo(User::class, 'medico_id', 'id');
    }

    public function turnos()
    {
        return $this->hasMany(Turno::class);
    }
}
