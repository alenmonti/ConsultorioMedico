<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Traits\RedirectAfterSubmit;

class CreateUser extends CreateRecord
{
    use RedirectAfterSubmit;
    protected static string $resource = UserResource::class;
}
