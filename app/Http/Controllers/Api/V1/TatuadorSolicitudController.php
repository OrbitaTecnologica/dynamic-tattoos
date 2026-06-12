<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\TatuadorSolicitudRequest;
use App\Mail\TatuadorSolicitudMail;
use App\Models\TatuadorSolicitud;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;

final class TatuadorSolicitudController extends Controller
{
    public function store(TatuadorSolicitudRequest $request): JsonResponse
    {
        $solicitud = TatuadorSolicitud::create(
            $request->validated() + ['status' => TatuadorSolicitud::STATUS_PENDING],
        );

        Mail::to((string) config('contact.to'))->send(new TatuadorSolicitudMail($solicitud));

        return response()->json(['message' => 'ok'], 201);
    }
}
