<?php

namespace Tests\Feature;

use App\Enums\Roles;
use App\Models\Horario;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_user_gets_medico_role_by_default(): void
    {
        $user = User::factory()->create();

        $this->assertEquals(Roles::Medico, $user->fresh()->rol);
    }

    public function test_medico_gets_self_referential_medico_id(): void
    {
        $user = User::factory()->create(['rol' => Roles::Medico]);

        $this->assertEquals($user->id, $user->fresh()->medico_id);
    }

    public function test_medico_gets_default_weekday_horarios(): void
    {
        $user = User::factory()->create(['rol' => Roles::Medico]);

        $horarios = Horario::withoutGlobalScopes()->where('medico_id', $user->id)->get();

        $this->assertCount(5, $horarios);

        $dias = $horarios->pluck('dia')->map(fn($d) => $d instanceof \BackedEnum ? $d->value : $d)->toArray();
        $this->assertEqualsCanonicalizing(['lunes', 'martes', 'miercoles', 'jueves', 'viernes'], $dias);
    }

    public function test_default_horarios_have_correct_times(): void
    {
        $user = User::factory()->create(['rol' => Roles::Medico]);

        $horario = Horario::withoutGlobalScopes()
            ->where('medico_id', $user->id)
            ->where('dia', 'lunes')
            ->first();

        $this->assertEquals('09:00', $horario->desde);
        $this->assertEquals('18:00', $horario->hasta);
        $this->assertEquals('00:20', $horario->intervalo);
    }

    public function test_admin_user_does_not_get_default_horarios(): void
    {
        $user = User::factory()->create(['rol' => Roles::Admin]);

        $horarios = Horario::withoutGlobalScopes()->where('medico_id', $user->id)->get();

        $this->assertEmpty($horarios);
    }

    public function test_secretario_does_not_get_default_horarios(): void
    {
        $user = User::factory()->create(['rol' => Roles::Secretario]);

        $horarios = Horario::withoutGlobalScopes()->where('medico_id', $user->id)->get();

        $this->assertEmpty($horarios);
    }
}
