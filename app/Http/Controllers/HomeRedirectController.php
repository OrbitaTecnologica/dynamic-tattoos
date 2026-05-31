<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

/**
 * Redirige la raíz al panel admin. Usa el generador de URLs (route()) en vez de
 * una ruta absoluta para respetar el prefijo de despliegue (p.ej. /backend) y la
 * base que Laravel detecta del request / APP_URL.
 */
final class HomeRedirectController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        return redirect()->route('admin.dashboard');
    }
}
