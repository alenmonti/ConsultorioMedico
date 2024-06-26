<?php

namespace App\Observers;

class UserObserver
{
    public function creating($model)
    {
        $model->rol = 'medico';
    }
}
