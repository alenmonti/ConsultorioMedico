<?php

namespace App\Models;

use App\Models\Scopes\Own;
use Illuminate\Database\Eloquent\Model;

class AperturaMensual extends Model
{
    protected $table = 'aperturas_mensuales';

    protected $fillable = [
        'medico_id',
        'anio',
        'mes',
        'abierto',
    ];

    protected $casts = [
        'abierto' => 'boolean',
    ];

    public function medico()
    {
        return $this->belongsTo(User::class, 'medico_id', 'id');
    }

    public static function booted(): void
    {
        static::creating(function ($apertura) {
            $apertura->medico_id = $apertura->medico_id ?? auth()->user()->medico_id;
        });

        static::addGlobalScope(Own::class);
    }
}
