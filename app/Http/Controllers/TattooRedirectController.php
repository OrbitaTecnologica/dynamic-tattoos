<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Tattoo;
use App\Models\TattooContent;
use App\Models\TattooScan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * TattooRedirectController – single-action (invokable) controller
 *
 * Handles every incoming QR scan (GET /t/{shortCode}).
 *
 * Decision tree:
 *   1. Validate short_code format (alphanumeric) to prevent injection.
 *   2. Resolve the active TattooContent via Cache::remember (5-minute TTL).
 *   3. If content is a "Link" type → 302 redirect to the external URL.
 *      A scheme whitelist (http/https) prevents open-redirect to javascript:
 *      or data: URIs (OWASP A01 / CWE-601).
 *   4. If gallery/video and the SPA frontend URL is configured → 302 redirect
 *      to {FRONTEND_URL}/t/{shortCode} so the branded gallery view renders.
 *   5. Otherwise (no FRONTEND_URL) → fall back to tattoo/show.blade.php which
 *      mounts <livewire:tattoo-viewer>.
 */
final class TattooRedirectController extends Controller
{
    /** Cache TTL in seconds (5 minutes). Adjust to taste. */
    private const CACHE_TTL_SECONDS = 300;

    public function __invoke(Request $request, string $shortCode): RedirectResponse|View
    {
        // Strict format validation – prevents SQL wildcard abuse and path traversal
        if (! preg_match('/^[a-zA-Z0-9]{1,12}$/', $shortCode)) {
            abort(404);
        }

        $content = $this->resolveActiveContent($shortCode);

        if ($content === null) {
            abort(404);
        }

        $this->recordScan($request, $content->tattoo_id);

        if ($content->isDirectRedirect()) {
            return $this->handleLinkRedirect($content);
        }

        $frontendRedirect = $this->buildFrontendRedirect($shortCode);

        if ($frontendRedirect !== null) {
            return redirect()->away($frontendRedirect, 302);
        }

        return view('tattoo.show', [
            'content'   => $content,
            'shortCode' => $shortCode,
        ]);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Fetches the active TattooContent for a given short_code, wrapping the
     * database query in a Cache::remember block to minimise DB hits on every
     * QR scan.
     *
     * The cache key "tattoo_content_{short_code}" is explicitly busted in
     * ManageTattoo::save() so changes are visible on the next scan.
     */
    private function resolveActiveContent(string $shortCode): ?TattooContent
    {
        $cacheKey = "tattoo_content_{$shortCode}";

        /** @var TattooContent|null */
        return Cache::remember(
            $cacheKey,
            self::CACHE_TTL_SECONDS,
            static function () use ($shortCode): ?TattooContent {
                return Tattoo::query()
                    ->active()
                    ->byShortCode($shortCode)
                    ->with(['activeContent'])
                    ->first()
                    ?->activeContent;
            }
        );
    }

    /**
     * Records the QR scan event asynchronously (best-effort; does not block
     * the redirect). Only captures IP, user-agent and derived device/browser
     * metadata — no PII beyond what is necessary for analytics.
     */
    private function recordScan(Request $request, int $tattooId): void
    {
        $userAgent = substr((string) $request->userAgent(), 0, 512);

        TattooScan::create([
            'tattoo_id'   => $tattooId,
            'ip_address'  => $request->ip(),
            'device_type' => TattooScan::detectDevice($userAgent),
            'browser'     => TattooScan::detectBrowser($userAgent),
            'user_agent'  => $userAgent,
            'scanned_at'  => now(),
        ]);
    }

    /**
     * Validates the redirect target and issues the 302.
     * Rejects any URL whose scheme is not http or https to block open-redirect
     * to non-web protocols (OWASP A01:2021 / CWE-601).
     */
    private function handleLinkRedirect(TattooContent $content): RedirectResponse
    {
        $rawUrl = $content->payload['url'] ?? '';

        $url = filter_var($rawUrl, FILTER_VALIDATE_URL);

        if ($url === false) {
            abort(404);
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        if (! in_array($scheme, ['http', 'https'], strict: true)) {
            abort(404);
        }

        return redirect()->away($url, 302);
    }

    /**
     * Builds the absolute URL of the SPA gallery view for this short code,
     * or null if the frontend host is not configured. The scheme whitelist
     * keeps us safe from operator-controlled open redirects via .env.
     */
    private function buildFrontendRedirect(string $shortCode): ?string
    {
        $base = config('services.frontend.url');

        if (! is_string($base) || $base === '') {
            return null;
        }

        $scheme = parse_url($base, PHP_URL_SCHEME);

        if (! in_array($scheme, ['http', 'https'], strict: true)) {
            return null;
        }

        return rtrim($base, '/').'/t/'.$shortCode;
    }
}
