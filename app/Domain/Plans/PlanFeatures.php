<?php

declare(strict_types=1);

namespace App\Domain\Plans;

/**
 * Authoritative table of which tattoo-content types each plan can publish.
 * Used by the API to enforce upgrades on `POST /api/v1/tattoos/{tattoo}/contents`
 * and by `/api/v1/me` (via UserResource) so the SPA can gate the QR Studio UI
 * without having to duplicate this knowledge.
 *
 * Plan slugs are the ones defined in `PlanSeeder` (start, plus, pro, empresa,
 * embajador). Unknown slugs are treated as having no capabilities — the API
 * will reject the request, the UI will show the upgrade modal.
 */
final class PlanFeatures
{
    public const TYPE_LINK = 'link';
    public const TYPE_GALLERY = 'gallery';
    public const TYPE_VIDEO = 'video';

    /**
     * @var array<string, list<string>>
     */
    private const ALLOWED_CONTENT_TYPES = [
        'embajador' => [self::TYPE_LINK],
        'start'     => [self::TYPE_LINK, self::TYPE_GALLERY],
        'plus'      => [self::TYPE_LINK, self::TYPE_GALLERY, self::TYPE_VIDEO],
        'pro'       => [self::TYPE_LINK, self::TYPE_GALLERY, self::TYPE_VIDEO],
        'empresa'   => [self::TYPE_LINK, self::TYPE_GALLERY, self::TYPE_VIDEO],
    ];

    /**
     * Plan slugs ordered from lower to higher tier. Used to compute the
     * "minimum required plan" for a given feature.
     *
     * @var list<string>
     */
    private const PLAN_ORDER = ['embajador', 'start', 'plus', 'pro', 'empresa'];

    public static function planAllowsContent(?string $planSlug, string $contentType): bool
    {
        if ($planSlug === null) {
            return false;
        }

        return in_array($contentType, self::ALLOWED_CONTENT_TYPES[$planSlug] ?? [], true);
    }

    /**
     * Returns the slug of the cheapest plan that includes the given content
     * type, or null if no plan supports it.
     */
    public static function minimumPlanForContent(string $contentType): ?string
    {
        foreach (self::PLAN_ORDER as $slug) {
            if (in_array($contentType, self::ALLOWED_CONTENT_TYPES[$slug] ?? [], true)) {
                return $slug;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    public static function allowedContentTypes(?string $planSlug): array
    {
        if ($planSlug === null) {
            return [];
        }

        return self::ALLOWED_CONTENT_TYPES[$planSlug] ?? [];
    }
}
