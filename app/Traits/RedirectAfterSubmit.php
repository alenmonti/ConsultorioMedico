<?php

namespace App\Traits;

trait RedirectAfterSubmit
{
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
