<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Roles;
use App\Models\Scopes\Own;
use App\Services\ScheduleService;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements HasAvatar, FilamentUser
{
    use HasFactory, Notifiable;

    public function getFilamentAvatarUrl(): string
    {
        return 'https://ui-avatars.com/api/?name='.$this->name.'&length=1&color=ffffff&rounded=false&background=166FA3 ';
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'surname',
        'email',
        'rol',
        'medico_id',
        'password',
        'especialidad',
        'descripcion',
        'foto_portal',
        'whatsapp',
        'monto_senia',
        'alias_pago',
        'portal_dias_anticipacion',
        'portal_dias_excluidos',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'rol' => Roles::class,
            'portal_dias_excluidos' => 'array',
        ];
    }

    public function turnos()
    {
        return $this->hasMany(Turno::class, 'medico_id', 'id');
    }

    public function pacientes()
    {
        return $this->hasMany(Paciente::class);
    }

    public function horarios()
    {
        return $this->hasMany(Horario::class, 'medico_id', 'id');
    }

    public function practicas()
    {
        return $this->hasMany(Practica::class, 'medico_id', 'id');
    }

    public function medico()
    {
        return $this->belongsTo(User::class, 'medico_id', 'id');
    }

    public function horariosDisponibles($fecha, $turnoTipo = 'turno', $duracion = 20)
    {
        return app(ScheduleService::class)->horariosDisponibles($this, $fecha, $turnoTipo, $duracion);
    }

    public function diasNoDisponibles($desde, $hasta)
    {
        return app(ScheduleService::class)->diasNoDisponibles($this, $desde, $hasta);
    }
}
