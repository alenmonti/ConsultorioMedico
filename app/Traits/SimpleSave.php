<?php

namespace App\Traits;

trait SimpleSave
{
    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCancelFormAction(),
        ];
    }
}
