<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Upload;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Stores user uploads on the public disk while keeping the `uploads` ledger
 * (used for storage-quota accounting) in sync and removing superseded files.
 */
final class UploadManager
{
    public function store(User $user, UploadedFile $file, string $type, string $directory, string $disk = 'public'): Upload
    {
        $this->purge($user, $type);

        $path = $file->store($directory, $disk);

        return $user->uploads()->create([
            'path' => $path,
            'disk' => $disk,
            'bytes' => (int) $file->getSize(),
            'type' => $type,
        ]);
    }

    /**
     * Remove every stored file + ledger row of the given type for the user.
     */
    public function purge(User $user, string $type): void
    {
        $user->uploads()
            ->where('type', $type)
            ->get()
            ->each(function (Upload $upload): void {
                Storage::disk($upload->disk)->delete($upload->path);
                $upload->delete();
            });
    }

    /**
     * Remove every stored file + ledger row of the given type across all users.
     * Needed when the owning entity is deleted outright: `uploads` has no FK to
     * it and the owner may be soft-deleted, so the user-scoped purge no basta.
     */
    public function purgeByType(string $type): void
    {
        Upload::query()
            ->where('type', $type)
            ->get()
            ->each(function (Upload $upload): void {
                Storage::disk($upload->disk)->delete($upload->path);
                $upload->delete();
            });
    }
}
