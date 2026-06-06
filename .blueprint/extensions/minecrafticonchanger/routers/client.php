<?php

use Pterodactyl\Http\Middleware\Activity\ServerSubject;
use Pterodactyl\Http\Middleware\Api\Client\Server\ResourceBelongsToServer;
use Pterodactyl\Http\Middleware\Api\Client\Server\AuthenticateServerAccess;
use Illuminate\Support\Facades\Route;
use Pterodactyl\BlueprintFramework\Extensions\minecrafticonchanger;

Route::group([
	'prefix' => '/servers/{server}',
	'middleware' => [
		ServerSubject::class,
		AuthenticateServerAccess::class,
		ResourceBelongsToServer::class,
	],
], function () {
	Route::get('/', [minecrafticonchanger\MinecraftIconChangerController::class, 'index']);
	Route::patch('/', [minecrafticonchanger\MinecraftIconChangerController::class, 'update']);
});
