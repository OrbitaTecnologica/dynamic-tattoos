<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreLinkPageLinkRequest;
use App\Http\Requests\Api\V1\UpdateLinkPageLinkRequest;
use App\Http\Resources\Api\V1\LinkPageLinkResource;
use App\Models\LinkPage;
use App\Models\LinkPageLink;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class LinkPageLinkController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $page = $this->resolvePage($request->user());

        return LinkPageLinkResource::collection($page->links()->get());
    }

    public function store(StoreLinkPageLinkRequest $request): JsonResponse
    {
        $page = $this->resolvePage($request->user());

        $link = $page->links()->create([
            'type' => (string) $request->input('type'),
            'label' => $request->input('label'),
            'value' => (string) $request->input('value'),
            'custom_icon' => $request->input('custom_icon'),
            'is_active' => (bool) $request->boolean('is_active', true),
            'position' => (int) ($page->links()->max('position') ?? 0) + 1,
        ]);

        return response()->json([
            'data' => new LinkPageLinkResource($link),
        ], 201);
    }

    public function update(UpdateLinkPageLinkRequest $request, LinkPageLink $link): JsonResponse
    {
        $this->authorize('update', $link);

        $link->update($request->validated());

        return response()->json([
            'data' => new LinkPageLinkResource($link),
        ]);
    }

    public function destroy(Request $request, LinkPageLink $link): JsonResponse
    {
        $this->authorize('delete', $link);

        $link->delete();

        return response()->json([], 204);
    }

    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        $page = $this->resolvePage($request->user());

        foreach (array_values($validated['ids']) as $position => $id) {
            $page->links()->where('id', $id)->update(['position' => $position + 1]);
        }

        return response()->json([
            'data' => LinkPageLinkResource::collection($page->links()->get()),
        ]);
    }

    private function resolvePage(User $user): LinkPage
    {
        return LinkPage::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['slug' => 'u'.$user->id, 'title' => $user->name],
        );
    }
}
