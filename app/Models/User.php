<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Roles;
use App\Models\Scopes\Own;
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
        'medico_id',
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

    public function medico()
    {
        return $this->belongsTo(User::class, 'medico_id', 'id');
    }

    public function horariosDisponibles($fecha, $turnoTipo = 'turno')
    {
        $enDay = Carbon::parse($fecha)->dayOfWeek;
        $diaSemana = match ($enDay) { 0 => 'domingo', 1 => 'lunes', 2 => 'martes', 3 => 'miercoles', 4 => 'jueves', 5 => 'viernes', 6 => 'sabado',};
        $horarios = [];
        $configHorarios = Horario::where('dia', $diaSemana)->get();
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

        $horariosOcupados = Turno::where('fecha', $fecha)->pluck('hora')->toArray();

        if($turnoTipo != 'turno'){
            $horariosDisponibles = array_intersect($horarios, $horariosOcupados);
        } else {
            $horariosDisponibles = array_diff($horarios, $horariosOcupados);
        }

        return array_combine($horariosDisponibles, $horariosDisponibles);
    }

    public function diasDeSemanaDisponibles()
    {
        $horarios = Horario::get();
        $dias = $horarios->map(function ($horario) {
            return match($horario->dia->getLabel()) { 'Domingo' => 0, 'Lunes' => 1, 'Martes' => 2, 'Miercoles' => 3, 'Jueves' => 4, 'Viernes' => 5, 'Sabado' => 6,};
        });
        return $dias->toArray();
    }

    public function diasNoDisponibles($desde, $hasta)
    {
        $fechaDesde = Carbon::parse($desde);
        $fechaHasta = Carbon::parse($hasta);
        $dias = [];
        while ($fechaDesde <= $fechaHasta) {
            if(!$this->horariosDisponibles($fechaDesde)){
                $dias[] = $fechaDesde->format('Y-m-d');
            }
            $fechaDesde = $fechaDesde->addDay();
        }
        return $dias;
    }
}
