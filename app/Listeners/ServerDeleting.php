<?php

namespace Pterodactyl\Listeners;

use Pterodactyl\Models\Subdomain;
use Pterodactyl\Models\Server;
use Pterodactyl\Events\Server\Deleting;

class ServerDeleting
{
    public function handle(Deleting $event)
    {
        // Needed so it triggers the deleting event on the subdomains
        Subdomain::where('server_id', $event->server->id)->get()->each->delete();
    }
}