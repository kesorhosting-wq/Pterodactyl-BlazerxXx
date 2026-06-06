<?php

namespace Pterodactyl\Http\Requests\Api\Client\Servers\Modpacks;

use Pterodactyl\Models\Permission;
use Pterodactyl\Http\Requests\Api\Client\ClientApiRequest;

class InstallModpackRequest extends ClientApiRequest
{
    public function permission(): string
    {
        return Permission::ACTION_FILE_CREATE;
    }

    public function rules(): array
    {
        return [
            'provider' => 'required|string|in:modrinth,curseforge',
            'project_id' => 'required|string|max:128',
            'version_id' => 'required|string|max:128',
            'wipe_files' => 'sometimes|boolean',
        ];
    }
}
