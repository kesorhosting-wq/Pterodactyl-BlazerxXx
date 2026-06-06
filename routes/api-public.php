<?php

use Illuminate\Support\Facades\Route;
use Pterodactyl\Http\Controllers\User\LanguageController;

Route::get('/locales/list', [LanguageController::class, 'index']);