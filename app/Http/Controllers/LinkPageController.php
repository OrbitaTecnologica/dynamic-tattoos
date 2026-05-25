<?php

namespace App\Http\Controllers;

use App\Models\LinkPage;
use App\Models\LinkPageClick;
use App\Models\LinkPageLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LinkPageController extends Controller
{
    public function show(string $slug): View
    {
        $page = LinkPage::with(['links' => fn ($q) => $q->where('is_active', true)->orderBy('position')])
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        // Incremento atómico de visitas (sin tocar updated_at).
        LinkPage::where('id', $page->id)->increment('views_count');

        return view('public.link-page', compact('page'));
    }

    public function redirect(Request $request, string $slug, int $link): RedirectResponse
    {
        $page = LinkPage::where('slug', $slug)->where('is_published', true)->firstOrFail();

        /** @var LinkPageLink $linkModel */
        $linkModel = $page->links()->where('is_active', true)->whereKey($link)->firstOrFail();

        // Analítica básica.
        LinkPageLink::where('id', $linkModel->id)->increment('clicks_count');
        LinkPageClick::create([
            'link_page_link_id' => $linkModel->id,
            'ip'                => $request->ip(),
            'referrer'          => substr((string) $request->headers->get('referer'), 0, 250) ?: null,
            'user_agent'        => substr((string) $request->userAgent(), 0, 250) ?: null,
        ]);

        return redirect()->away($linkModel->href(), 302);
    }
}
