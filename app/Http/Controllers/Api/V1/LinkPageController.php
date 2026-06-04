<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateLinkPageRequest;
use App\Http\Resources\Api\V1\LinkPageResource;
use App\Models\LinkPage;
use App\Models\User;
use App\Services\UploadManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

    public function uploadAvatar(Request $request, UploadManager $uploads): JsonResponse
    {
        return $this->storeImage($request, $uploads, 'avatar_path', 'avatar', 'link_avatar');
    }

    public function uploadCover(Request $request, UploadManager $uploads): JsonResponse
    {
        return $this->storeImage($request, $uploads, 'cover_path', 'cover', 'link_cover');
    }

    public function deleteAvatar(Request $request): JsonResponse
    {
        return $this->clearImage($request, 'avatar_path');
    }

    public function deleteCover(Request $request): JsonResponse
    {
        return $this->clearImage($request, 'cover_path');
    }

    private function clearImage(Request $request, string $column): JsonResponse
    {
        $page = $this->resolvePage($request->user());
        $page->update([$column => null]);
        $page->load('links');

        return response()->json([
            'data' => new LinkPageResource($page),
        ]);
    }

    private function storeImage(Request $request, UploadManager $uploads, string $column, string $field, string $type): JsonResponse
    {
        $request->validate([
            $field => ['required', 'image', 'max:5120'],
        ]);

        $user = $request->user();
        $page = $this->resolvePage($user);
        $upload = $uploads->store($user, $request->file($field), $type, 'link-pages');
        $page->update([$column => $upload->path]);
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
