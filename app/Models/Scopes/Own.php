<?php

namespace App\Models\Scopes;

use App\Enums\Roles;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class Own implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (!role(Roles::Admin)) {
            $builder->where('medico_id', auth()->user()->medico_id);
        }
    }
}
