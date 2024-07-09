<?php

namespace App\Models;

use App\Casts\TimeCast;
use App\Enums\Dias;
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

    public static function booted()
    {
        static::creating(function ($horario) {
            $horario->medico_id = auth()->id();
        });
    }
}
