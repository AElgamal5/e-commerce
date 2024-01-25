<?php

namespace App\Services\v1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ImageService
{

    public function save(string $image): string|JsonResponse
    {
        $maxFileSize = env('MAX_FILE_SIZE', 10);
        $maxImageSize = $maxFileSize * 1000000;

        $imageData = base64_decode(preg_replace('/^data:image\/(\w+);base64,/', '', $image));

        if (strlen($imageData) > $maxImageSize) {
            return response()
                ->json(
                    ['message' => "Image size exceeds maximum image size: {$maxFileSize}MB"],
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
        }

        $uniqueId = uniqid();
        $imageName = time() . '_' . $uniqueId . '.png';
        Storage::disk('public')->put($imageName, $imageData);

        return $imageName;
    }
    public function delete(string $imageName): void
    {
        if (Storage::disk('public')->exists($imageName)) {
            Storage::disk('public')->delete($imageName);
        }
    }
}