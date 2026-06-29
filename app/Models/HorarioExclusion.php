<?php

namespace App\Models;

use App\Models\Scopes\Own;
use Illuminate\Database\Eloquent\Model;

class HorarioExclusion extends Model
{
    protected $table = 'horarios_excluidos';

    protected $fillable = [
        'medico_id',
        'fecha',
        'todo_el_dia',
        'desde',
        'hasta',
        'motivo',
    ];

    protected $casts = [
        'fecha' => 'date',
        'todo_el_dia' => 'boolean',
    ];

    public function medico()
    {
        return $this->belongsTo(User::class, 'medico_id', 'id');
    }

    public static function booted(): void
    {
        static::creating(function ($exclusion) {
            $exclusion->medico_id = $exclusion->medico_id ?? auth()->user()->medico_id;
        });

        static::addGlobalScope(Own::class);
    }
}
