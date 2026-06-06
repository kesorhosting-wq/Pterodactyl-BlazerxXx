<?php

namespace Pterodactyl\Http\Requests\Api\Client\Servers\BedrockVersions;

use Pterodactyl\Models\Permission;
use Pterodactyl\Http\Requests\Api\Client\ClientApiRequest;

class ListBedrockVersionsRequest extends ClientApiRequest
{
    public function permission(): string
    {
        return Permission::ACTION_FILE_READ;
    }
}
