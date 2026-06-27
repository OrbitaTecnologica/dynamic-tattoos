<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreQrCodeRequest;
use App\Http\Resources\Api\V1\QrCodeResource;
use App\Mail\QrCodeMail;
use App\Models\QrCode;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Mail;

final class QrCodeController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = $request->user();

        $qrCodes = QrCode::query()
            ->forUser($user->id)
            ->latest()
            ->paginate(20);

        return QrCodeResource::collection($qrCodes);
    }

    public function store(StoreQrCodeRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $qrCode = QrCode::query()->create([
            'user_id' => $user->id,
            'slug' => $request->input('slug'),
            'name' => $request->input('name'),
            'url' => (string) $request->input('url'),
            'color' => (string) $request->input('color', '#1a1a1a'),
            'dots_type' => (string) $request->input('dots_type', 'square'),
            'corners_square_type' => (string) $request->input('corners_square_type', 'square'),
            'corners_dot_type' => (string) $request->input('corners_dot_type', 'square'),
        ]);

        activity('qr')
            ->causedBy($user)
            ->event('created')
            ->withProperties(['detail' => (string) $qrCode->url])
            ->log('Nuevo QR generado');

        return response()->json([
            'data' => new QrCodeResource($qrCode),
        ], 201);
    }

    public function update(Request $request, QrCode $qrCode): JsonResponse
    {
        $this->authorize('update', $qrCode);

        $validated = $request->validate([
            'url'                  => ['sometimes', 'required', 'string', 'max:2000'],
            'color'                => ['sometimes', 'required', 'string', 'max:50'],
            'dots_type'            => ['sometimes', 'required', 'string', 'max:50'],
            'corners_square_type'  => ['sometimes', 'required', 'string', 'max:50'],
            'corners_dot_type'     => ['sometimes', 'required', 'string', 'max:50'],
        ]);

        $qrCode->update($validated);

        activity('qr')
            ->causedBy($request->user())
            ->event('updated')
            ->withProperties(['detail' => (string) $qrCode->url])
            ->log('QR actualizado');

        return response()->json([
            'data' => new QrCodeResource($qrCode),
        ]);
    }

    public function show(Request $request, QrCode $qrCode): JsonResponse
    {
        $this->authorize('view', $qrCode);

        return response()->json([
            'data' => new QrCodeResource($qrCode),
        ]);
    }

    public function destroy(Request $request, QrCode $qrCode): JsonResponse
    {
        $this->authorize('delete', $qrCode);

        $qrCode->delete();

        return response()->json([], 204);
    }

    public function slugAvailable(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'slug' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9._~\-\/]+$/'],
        ]);

        $slug = (string) $validated['slug'];
        $available = ! QrCode::query()->where('slug', $slug)->exists();

        return response()->json([
            'data' => [
                'slug' => $slug,
                'available' => $available,
            ],
        ]);
    }

    public function email(Request $request, QrCode $qrCode): JsonResponse
    {
        $this->authorize('view', $qrCode);

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'image' => ['required', 'string'],
        ]);

        Mail::to($validated['email'])->send(new QrCodeMail($validated['image'], (string) $qrCode->url));

        return response()->json([
            'message' => 'QR enviado correctamente.',
        ]);
    }
}
