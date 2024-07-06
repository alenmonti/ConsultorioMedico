<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Roles;
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
}
