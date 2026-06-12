<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Tattoo;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Stores image/video files uploaded from the SPA QR Studio so the editor can
 * later call `POST /api/v1/tattoos/{tattoo}/contents/activate` with stable
 * public URLs in the payload. Designed to be called once per file (the SPA
 * sends them in parallel) to keep individual requests small.
 */
final class TattooContentUploadController extends Controller
{
    public function __invoke(Request $request, Tattoo $tattoo): JsonResponse
    {
        $this->authorize('update', $tattoo);

        $request->validate([
            'file' => [
                'required', 'file',
                'mimes:jpeg,jpg,png,webp,gif,mp4,mov,webm,quicktime',
                'max:51200', // 50 MB; el plan límite total se verifica al activar.
            ],
        ]);

        /** @var User $user */
        $user = $request->user();
        $file = $request->file('file');
        $path = $file->store("tattoos/{$tattoo->id}", 'public');

        $user->uploads()->create([
            'path'  => $path,
            'disk'  => 'public',
            'bytes' => (int) $file->getSize(),
            'type'  => "tattoo:{$tattoo->id}",
        ]);

        return response()->json([
            'data' => [
                'url'   => Storage::disk('public')->url($path),
                'path'  => $path,
                'name'  => $file->getClientOriginalName(),
                'mime'  => $file->getMimeType(),
                'bytes' => (int) $file->getSize(),
            ],
        ], 201);
    }
}
