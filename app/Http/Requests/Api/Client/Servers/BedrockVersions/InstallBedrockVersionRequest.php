<?php

namespace Pterodactyl\Http\Requests\Api\Client\Servers\BedrockVersions;

use Pterodactyl\Models\Permission;
use Pterodactyl\Http\Requests\Api\Client\ClientApiRequest;

class InstallBedrockVersionRequest extends ClientApiRequest
{
    public function permission(): string
    {
        return Permission::ACTION_FILE_CREATE;
    }

    public function rules(): array
    {
        return [
            'channel' => 'required|string|in:release,preview',
            'version' => 'required|string|max:32',
            'wipe_files' => 'sometimes|boolean',
        ];
    }
}
