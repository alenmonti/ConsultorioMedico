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
        $diaSemana = match ($enDay) {
            0 => 'domingo',
            1 => 'lunes',
            2 => 'martes',
            3 => 'miercoles',
            4 => 'jueves',
            5 => 'viernes',
            6 => 'sabado',
        };
        $horarios = [];
        $defaultHorarios = [ '08:00', '08:20', '08:40', '09:00', '09:20', '09:40', '10:00', '10:20', '10:40', '11:00', '11:20', '11:40', '12:00', '12:20', '12:40', '13:00', '13:20', '13:40', '14:00', '14:20', '14:40', '15:00', '15:20', '15:40', '16:00', '16:20', '16:40', '17:00', '17:20', '17:40', '18:00', '18:20', '18:40', '19:00', '19:20', '19:40', '20:00', '20:20', '20:40', '21:00', '21:20', '21:40', '22:00'];
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
            $horarios = $defaultHorarios;
        }

        $horariosOcupados = $this->turnos()->where('fecha', $fecha)->pluck('hora')->toArray();
        $horariosDisponibles = array_diff($horarios, $horariosOcupados);

        return array_combine($horariosDisponibles, $horariosDisponibles);
    }
}
