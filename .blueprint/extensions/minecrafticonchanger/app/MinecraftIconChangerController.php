<?php

namespace Pterodactyl\BlueprintFramework\Extensions\minecrafticonchanger;

use Pterodactyl\Models\Server;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Facades\Activity;
use Illuminate\Support\Facades\Request;
use Pterodactyl\Repositories\Wings\DaemonFileRepository;
use Pterodactyl\Http\Controllers\Api\Client\ClientApiController;
use Pterodactyl\BlueprintFramework\Libraries\ExtensionLibrary\Client\BlueprintClientLibrary;
use Pterodactyl\BlueprintFramework\Extensions\minecrafticonchanger\Requests\MinecraftIconChangerUpdateRequest;

class MinecraftIconChangerController extends ClientApiController
{
    public function __construct(
        private DaemonFileRepository $fileRepository,
        private BlueprintClientLibrary $blueprint,
    ) {
        parent::__construct();
    }

    private function resizeImage(string $path, string $target, int $maxWidth, int $maxHeight): void
    {
        list($origWidth, $origHeight, $type) = getimagesize($path);

        $newWidth = $maxWidth;
        $newHeight = $maxHeight;

        $newImage = imagecreatetruecolor($newWidth, $newHeight);

        if ($type === IMAGETYPE_PNG) {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
        }

        if ($type === IMAGETYPE_JPEG) {
            $image = imagecreatefromjpeg($path);
        } else {
            $image = imagecreatefrompng($path);
        }

        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
        imagepng($newImage, $target);

        imagedestroy($newImage);
        imagedestroy($image);
    }

    public function index(Request $request, Server $server): JsonResponse
    {
        $eggIds = json_decode($this->blueprint->dbGet('minecrafticonchanger', 'eggs') ?: '[]');

        return new JsonResponse([
            'minecraft' => in_array((string) $server->egg_id, $eggIds),
        ]);
    }

    public function update(MinecraftIconChangerUpdateRequest $request, Server $server): JsonResponse
    {
        $eggIds = json_decode($this->blueprint->dbGet('minecrafticonchanger', 'eggs') ?: '[]');

        if (!in_array((string) $server->egg_id, $eggIds)) {
            return new JsonResponse(['error' => 'This server is not a Minecraft server.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $image = base64_decode($request->input('image'));
        if (empty($image)) {
            return new JsonResponse(['error' => 'No image data provided.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        if (strlen($image) > 1024 * 1024 * 15) {
            return new JsonResponse(['error' => 'Image size is too large.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $tmpDir = storage_path('extensions/minecrafticonchanger');
        $tmpFileOld = sprintf('%s/%s-%s.old', $tmpDir, $server->id, time());
        $tmpFile = sprintf('%s/%s-%s.png', $tmpDir, $server->id, time());

        file_put_contents($tmpFileOld, $image);

        $type = exif_imagetype($tmpFileOld);

        if (!in_array($type, [IMAGETYPE_PNG, IMAGETYPE_JPEG])) {
            return new JsonResponse(['error' => 'Invalid image type provided.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $this->resizeImage($tmpFileOld, $tmpFile, 64, 64);

            $this->fileRepository->setServer($server)->putContent('server-icon.png', file_get_contents($tmpFile));
        } finally {
            unlink($tmpFileOld);
            unlink($tmpFile);
        }

        Activity::event('server:icon.change')->log();

        return new JsonResponse([], JsonResponse::HTTP_NO_CONTENT);
    }
}
