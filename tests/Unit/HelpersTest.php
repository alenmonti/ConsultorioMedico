<?php

namespace Tests\Unit;

use App\Enums\Roles;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class HelpersTest extends TestCase
{
    public function test_role_returns_false_when_unauthenticated(): void
    {
        Auth::shouldReceive('user')->andReturn(null);

        $this->assertFalse(role(Roles::Admin));
        $this->assertFalse(role(Roles::Medico));
    }

    public function test_role_returns_true_when_role_matches(): void
    {
        $user = new \App\Models\User(['rol' => Roles::Medico]);
        Auth::shouldReceive('user')->andReturn($user);

        $this->assertTrue(role(Roles::Medico));
    }

    public function test_role_returns_false_when_role_does_not_match(): void
    {
        $user = new \App\Models\User(['rol' => Roles::Medico]);
        Auth::shouldReceive('user')->andReturn($user);

        $this->assertFalse(role(Roles::Admin));
    }

    public function test_user_returns_null_when_unauthenticated(): void
    {
        Auth::shouldReceive('user')->andReturn(null);

        $this->assertNull(user());
    }
}
