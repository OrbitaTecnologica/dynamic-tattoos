<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Mail\TatuadorApprovedMail;
use App\Models\TatuadorSolicitud;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

final class TatuadorApprovalController extends Controller
{
    /** Aprueba una solicitud de tatuador: crea el User con role=artist y envía email. */
    public function approve(TatuadorSolicitud $solicitud): JsonResponse
    {
        if ($solicitud->status !== TatuadorSolicitud::STATUS_PENDING) {
            abort(422, 'Solicitud ya procesada');
        }

        $email = mb_strtolower((string) $solicitud->email);

        // Si el email ya existe (p.ej. el tatuador era un cliente previo), promovemos su rol.
        $user = User::query()->where('email', $email)->first();

        if ($user === null) {
            $user = User::query()->create([
                'name' => (string) $solicitud->name,
                'email' => $email,
                'password' => Hash::make(Str::random(64)),
                'role' => 'artist',
            ]);
        } else {
            $user->forceFill(['role' => 'artist'])->save();
        }

        $solicitud->forceFill([
            'status' => TatuadorSolicitud::STATUS_APPROVED,
            'approved_at' => now(),
            'user_id' => $user->id,
        ])->save();

        // Token de reset password para que el tatuador defina su contraseña al primer acceso.
        $token = Password::broker()->createToken($user);

        Mail::to($user->email)->send(new TatuadorApprovedMail($user, $token));

        activity('account')
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->event('tatuador_approved')
            ->withProperties(['solicitud_id' => $solicitud->id])
            ->log('Tatuador aprobado');

        return response()->json([
            'data' => [
                'user_id' => $user->id,
                'solicitud_id' => $solicitud->id,
            ],
        ]);
    }
}
