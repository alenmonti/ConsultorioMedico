<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('linkstorage', function () {
    Artisan::call('storage:link');
    return 'Storage linked successfully.';
});
Route::get('migrate', function () {
    Artisan::call('migrate');
    return 'Database migrated successfully.';
});
