<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Mail\QrCodeMail;
use App\Models\QrCode;
use App\Models\Tattoo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

final class QrCodeController extends Controller
{
    public const BASE_URL = 'https://d-tattoo.com';

    public const DOTS_TYPES = [
        'dots', 'rounded', 'extra-rounded',
    ];

    public const CORNERS_SQUARE_TYPES = [
        'dot', 'extra-rounded',
    ];

    public const CORNERS_DOT_TYPES = [
        'dot',
    ];

    /**
     * Show the QR Studio, optionally pre-filling the slug from a tattoo short_code.
     * The ?tattoo query param accepts a validated short_code (1–12 alphanumeric chars).
     */
    public function create(Request $request): View
    {
        $initialSlug = '';

        $shortCode = $request->query('tattoo', '');

        if (is_string($shortCode) && preg_match('/^[a-zA-Z0-9]{1,12}$/', $shortCode)) {
            $tattoo = Tattoo::query()
                ->where('short_code', $shortCode)
                ->where('user_id', $request->user()->id)
                ->first();

            if ($tattoo !== null) {
                $initialSlug = 't/' . $shortCode;
            }
        }

        return view('qr.create', [
            'baseUrl'            => self::BASE_URL,
            'initialSlug'        => $initialSlug,
            'dotsTypes'          => self::DOTS_TYPES,
            'cornersSquareTypes' => self::CORNERS_SQUARE_TYPES,
            'cornersDotTypes'    => self::CORNERS_DOT_TYPES,
            'history'            => QrCode::latest()->limit(20)->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'slug'               => ['required', 'string', 'max:200', 'regex:/^[A-Za-z0-9._~\-\/]+$/'],
            'color'              => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'dots_type'          => ['required', 'in:' . implode(',', self::DOTS_TYPES)],
            'corners_square_type' => ['required', 'in:' . implode(',', self::CORNERS_SQUARE_TYPES)],
            'corners_dot_type'   => ['required', 'in:' . implode(',', self::CORNERS_DOT_TYPES)],
        ], [
            'slug.regex' => 'La ruta solo puede contener letras, números, guiones, puntos y barras.',
        ]);

        $data['url'] = self::BASE_URL . '/' . ltrim($data['slug'], '/');
        unset($data['slug']);

        $qr = QrCode::create($data);

        return response()->json([
            'message' => 'QR guardado correctamente',
            'qr'      => $qr,
        ], 201);
    }

    public function history(): JsonResponse
    {
        return response()->json(QrCode::latest()->limit(20)->get());
    }

    public function sendEmail(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email'    => ['required', 'email', 'max:254'],
            'qr_image' => ['required', 'string'],
            'url'      => ['required', 'url', 'max:2048'],
        ]);

        // Open-redirect guard: only allow http/https scheme URLs
        $scheme = parse_url($data['url'], PHP_URL_SCHEME);
        abort_unless(in_array($scheme, ['http', 'https'], true), 422, 'URL no permitida.');

        Mail::to($data['email'])->send(new QrCodeMail($data['qr_image'], $data['url']));

        return response()->json(['message' => 'QR enviado a ' . $data['email']]);
    }
}
