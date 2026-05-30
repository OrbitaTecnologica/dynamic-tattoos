<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateLinkPageRequest;
use App\Http\Resources\Api\V1\LinkPageResource;
use App\Models\LinkPage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class LinkPageController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $page = $this->resolvePage($request->user());
        $page->load('links');

        return response()->json([
            'data' => new LinkPageResource($page),
        ]);
    }

    public function update(UpdateLinkPageRequest $request): JsonResponse
    {
        $page = $this->resolvePage($request->user());
        $page->update($request->validated());
        $page->load('links');

        return response()->json([
            'data' => new LinkPageResource($page),
        ]);
    }

    public function uploadAvatar(Request $request): JsonResponse
    {
        return $this->storeImage($request, 'avatar_path', 'avatar');
    }

    public function uploadCover(Request $request): JsonResponse
    {
        return $this->storeImage($request, 'cover_path', 'cover');
    }

    private function storeImage(Request $request, string $column, string $field): JsonResponse
    {
        $request->validate([
            $field => ['required', 'image', 'max:5120'],
        ]);

        $page = $this->resolvePage($request->user());
        $path = $request->file($field)->store('link-pages', 'public');
        $page->update([$column => $path]);
        $page->load('links');

        return response()->json([
            'data' => new LinkPageResource($page),
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
