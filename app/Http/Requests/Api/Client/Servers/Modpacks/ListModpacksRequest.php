<?php

namespace Pterodactyl\Http\Requests\Api\Client\Servers\Modpacks;

use Pterodactyl\Models\Permission;
use Pterodactyl\Http\Requests\Api\Client\ClientApiRequest;

class ListModpacksRequest extends ClientApiRequest
{
    public function permission(): string
    {
        return Permission::ACTION_FILE_READ;
    }

    public function rules(): array
    {
        return [
            'provider' => 'required|string|in:modrinth,curseforge',
            'query' => 'nullable|string|max:128',
            'project_id' => 'nullable|string|max:128',
            'page' => 'sometimes|integer|min:1|max:500',
        ];
    }
}
