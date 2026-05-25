<?php

namespace App\Services;

/**
 * Temas predefinidos para la tarjeta pública.
 * Cada tema define un conjunto de variables CSS que se inyectan en
 * la vista pública y en el preview en vivo del editor.
 */
class LinkPageThemes
{
    public const DEFAULT_KEY = 'dtattoos';

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function all(): array
    {
        return [
            'dtattoos' => [
                'name'           => 'dtattoos',
                'description'    => 'Gradiente rojo oscuro firma de la casa.',
                'bg'             => 'radial-gradient(1200px 600px at 80% -10%, rgba(180,0,0,0.45), transparent 60%), radial-gradient(900px 500px at -10% 20%, rgba(220,0,0,0.20), transparent 60%), #0b0d12',
                'text_color'     => '#f8fafc',
                'muted_color'    => '#cbd5e1',
                'button_bg'      => 'linear-gradient(135deg, #b30000, #ff1a1a)',
                'button_text'    => '#ffffff',
                'button_radius'  => '14px',
                'button_style'   => 'solid',
                'font'           => 'Inter, system-ui, sans-serif',
                'fade'           => 'rgba(255,26,26,0.20)',
            ],
            'oscuro' => [
                'name'           => 'Oscuro',
                'description'    => 'Negro minimalista, alto contraste.',
                'bg'             => '#0b0d12',
                'text_color'     => '#f8fafc',
                'muted_color'    => '#94a3b8',
                'button_bg'      => '#1f2937',
                'button_text'    => '#f8fafc',
                'button_radius'  => '12px',
                'button_style'   => 'solid',
                'font'           => 'Inter, system-ui, sans-serif',
                'fade'           => 'rgba(255,255,255,0.08)',
            ],
            'claro' => [
                'name'           => 'Claro',
                'description'    => 'Fondo blanco limpio.',
                'bg'             => '#f8fafc',
                'text_color'     => '#0f172a',
                'muted_color'    => '#475569',
                'button_bg'      => '#0f172a',
                'button_text'    => '#ffffff',
                'button_radius'  => '12px',
                'button_style'   => 'solid',
                'font'           => 'Inter, system-ui, sans-serif',
                'fade'           => 'rgba(15,23,42,0.10)',
            ],
            'neon' => [
                'name'           => 'Neon',
                'description'    => 'Cyber con acento eléctrico y glow.',
                'bg'             => 'radial-gradient(70% 55% at 50% 0%, rgba(168,85,247,0.45), transparent 65%), radial-gradient(60% 50% at 80% 90%, rgba(34,211,238,0.30), transparent 65%), #060818',
                'text_color'     => '#f5f3ff',
                'muted_color'    => '#c4b5fd',
                'button_bg'      => 'linear-gradient(135deg, #22d3ee 0%, #a855f7 55%, #f472b6 100%)',
                'button_text'    => '#0b0220',
                'button_radius'  => '999px',
                'button_style'   => 'glow',
                'font'           => '"Space Grotesk", Inter, system-ui, sans-serif',
                'fade'           => 'rgba(168,85,247,0.45)',
            ],
            'pastel' => [
                'name'           => 'Pastel',
                'description'    => 'Suave, rosa, melocotón y crema.',
                'bg'             => 'radial-gradient(80% 50% at 20% 10%, #ffe4e6 0%, transparent 60%), radial-gradient(80% 50% at 80% 90%, #fde68a 0%, transparent 55%), linear-gradient(180deg, #fff1f2 0%, #fef3c7 100%)',
                'text_color'     => '#7c2d12',
                'muted_color'    => '#b45309',
                'button_bg'      => 'linear-gradient(135deg, #fb7185 0%, #f97316 100%)',
                'button_text'    => '#ffffff',
                'button_radius'  => '999px',
                'button_style'   => 'soft',
                'font'           => '"Quicksand", Inter, system-ui, sans-serif',
                'fade'           => 'rgba(251,113,133,0.28)',
            ],
            'glass' => [
                'name'           => 'Glass',
                'description'    => 'Cristal esmerilado sobre degradado vivo.',
                'bg'             => 'linear-gradient(135deg, #0ea5e9 0%, #6366f1 45%, #ec4899 100%)',
                'text_color'     => '#ffffff',
                'muted_color'    => '#e0e7ff',
                'button_bg'      => 'rgba(255,255,255,0.14)',
                'button_text'    => '#ffffff',
                'button_radius'  => '18px',
                'button_style'   => 'glass',
                'font'           => '"Montserrat", Inter, system-ui, sans-serif',
                'fade'           => 'rgba(255,255,255,0.30)',
            ],
            'retro' => [
                'name'           => 'Retro',
                'description'    => 'Setentas: mostaza, oliva, cartel vintage.',
                'bg'             => 'linear-gradient(180deg, #fbbf24 0%, #d97706 55%, #92400e 100%)',
                'text_color'     => '#1c1917',
                'muted_color'    => '#44403c',
                'button_bg'      => '#1c1917',
                'button_text'    => '#fde68a',
                'button_radius'  => '6px',
                'button_style'   => 'hard-shadow',
                'font'           => '"Bebas Neue", Impact, Inter, sans-serif',
                'fade'           => 'rgba(28,25,23,0.28)',
            ],
        ];
    }

    public static function keys(): array
    {
        return array_keys(self::all());
    }

    /**
     * Catálogo curado de fuentes que el usuario puede elegir.
     * key => ['label', 'stack', 'google' (familia en Google Fonts opcional)]
     *
     * @return array<string, array<string,string>>
     */
    public static function fonts(): array
    {
        return [
            'inter'    => ['label' => 'Inter (sans, moderna)',         'stack' => 'Inter, system-ui, sans-serif',                 'google' => 'Inter:wght@400;500;600;700'],
            'system'   => ['label' => 'System UI (nativa)',            'stack' => 'system-ui, -apple-system, Segoe UI, sans-serif','google' => ''],
            'poppins'  => ['label' => 'Poppins (geométrica)',          'stack' => '"Poppins", Inter, sans-serif',                  'google' => 'Poppins:wght@400;500;600;700'],
            'montser'  => ['label' => 'Montserrat (limpia)',           'stack' => '"Montserrat", Inter, sans-serif',               'google' => 'Montserrat:wght@400;500;600;700'],
            'space'    => ['label' => 'Space Grotesk (tech)',          'stack' => '"Space Grotesk", Inter, sans-serif',            'google' => 'Space+Grotesk:wght@400;500;600;700'],
            'quick'    => ['label' => 'Quicksand (redondeada)',        'stack' => '"Quicksand", Inter, sans-serif',                'google' => 'Quicksand:wght@400;500;600;700'],
            'playfair' => ['label' => 'Playfair Display (serif)',      'stack' => '"Playfair Display", Georgia, serif',            'google' => 'Playfair+Display:wght@400;500;600;700'],
            'bebas'    => ['label' => 'Bebas Neue (display)',          'stack' => '"Bebas Neue", Impact, sans-serif',              'google' => 'Bebas+Neue'],
            'jetb'     => ['label' => 'JetBrains Mono (mono)',         'stack' => '"JetBrains Mono", ui-monospace, monospace',     'google' => 'JetBrains+Mono:wght@400;500;700'],
        ];
    }

    /**
     * URL del CSS de Google Fonts agregando todas las familias del catálogo.
     */
    public static function fontsCssUrl(): string
    {
        $families = [];
        foreach (self::fonts() as $f) {
            if (! empty($f['google'])) {
                $families[] = 'family=' . $f['google'];
            }
        }
        return 'https://fonts.googleapis.com/css2?' . implode('&', $families) . '&display=swap';
    }

    public static function get(string $key): array
    {
        return self::all()[$key] ?? self::all()[self::DEFAULT_KEY];
    }

    /**
     * Devuelve el tema final mezclando los overrides del usuario sobre el preset.
     */
    public static function resolve(string $key, ?array $overrides = null): array
    {
        $base = self::get($key);

        if (! $overrides) {
            return $base;
        }

        return array_merge($base, array_filter($overrides, fn ($v) => $v !== null && $v !== ''));
    }

    /**
     * Compila variables CSS para inyectar en el documento.
     */
    public static function toCssVars(array $theme): string
    {
        $map = [
            '--lp-bg'             => $theme['bg'] ?? '#0b0d12',
            '--lp-text'           => $theme['text_color'] ?? '#f8fafc',
            '--lp-muted'          => $theme['muted_color'] ?? '#94a3b8',
            '--lp-btn-bg'         => $theme['button_bg'] ?? '#1f2937',
            '--lp-btn-text'       => $theme['button_text'] ?? '#ffffff',
            '--lp-btn-radius'     => $theme['button_radius'] ?? '12px',
            '--lp-font'           => $theme['font'] ?? 'Inter, system-ui, sans-serif',
            '--lp-fade'           => $theme['fade'] ?? 'rgba(255,255,255,0.10)',
        ];

        $parts = [];
        foreach ($map as $k => $v) {
            $parts[] = "{$k}: {$v};";
        }

        return implode(' ', $parts);
    }
}
