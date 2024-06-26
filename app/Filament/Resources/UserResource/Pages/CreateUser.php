<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Traits\{RedirectAfterSubmit, SimpleSave};

class CreateUser extends CreateRecord
{
    use RedirectAfterSubmit, SimpleSave;
    
    protected static string $resource = UserResource::class;
}
