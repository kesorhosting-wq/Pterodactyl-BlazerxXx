<?php

namespace Pterodactyl\BlueprintFramework\Extensions\minecrafticonchanger\Requests;

use Pterodactyl\Http\Requests\Api\Client\ClientApiRequest;

class MinecraftIconChangerUpdateRequest extends ClientApiRequest
{
    public function permission(): string
    {
        return 'file.create';
    }

    public function rules(): array
    {
        return [
            'image' => 'required|max:10485760',
        ];
    }
}
