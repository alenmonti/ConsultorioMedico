<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Roles;
use Carbon\Carbon;
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
        'password',
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

    public function horariosDisponibles($fecha)
    {
        $enDay = Carbon::parse($fecha)->dayOfWeek;
        $diaSemana = match ($enDay) { 0 => 'domingo', 1 => 'lunes', 2 => 'martes', 3 => 'miercoles', 4 => 'jueves', 5 => 'viernes', 6 => 'sabado',};
        $horarios = [];
        $configHorarios = $this->horarios()->where('dia', $diaSemana)->get();
        if(!$configHorarios->isEmpty()){
            $horariosArray = [];
            foreach ($configHorarios as $horario) {
                $desde = Carbon::parse($horario->desde);
                $hasta = Carbon::parse($horario->hasta);
                $intervalo = (int) Carbon::parse($horario->intervalo)->format('i');
                while ($desde <= $hasta) {
                    $horariosArray[] = $desde->format('H:i');
                    $desde = $desde->addMinutes($intervalo);
                }
            }
            $horarios = $horariosArray;
        }else{
            $horarios = [];
        }

        $horariosOcupados = $this->turnos()->where('fecha', $fecha)->pluck('hora')->toArray();
        $horariosDisponibles = array_diff($horarios, $horariosOcupados);

        return array_combine($horariosDisponibles, $horariosDisponibles);
    }
}
