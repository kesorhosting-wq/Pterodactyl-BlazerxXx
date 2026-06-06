<?php

use Illuminate\Support\Facades\Route;
use Pterodactyl\BlueprintFramework\Extensions\mcmods;

Route::group(['prefix' => '/servers/{server}'], function () {
        Route::get('/', [mcmods\ModsManagerController::class, 'index']);
        Route::get('/version', [mcmods\ModsVersionsController::class, 'index']);
        Route::post('/install', [mcmods\InstallModsController::class, 'index']);
        Route::get('/settings', [mcmods\MCModsSettingsController::class, 'index']);
});
