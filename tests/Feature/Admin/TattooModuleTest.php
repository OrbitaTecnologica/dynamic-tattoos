<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Livewire\Admin\TattooList;
use App\Models\Tattoo;
use App\Models\TattooContent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

final class TattooModuleTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    /** @return array{0: User, 1: Tattoo} dueño + tatuaje con un archivo subido y su fila de ledger */
    private function tattooWithUpload(): array
    {
        $owner = User::factory()->create();
        $tattoo = Tattoo::create([
            'user_id' => $owner->id,
            'name' => 'Con archivos',
            'short_code' => 'FIL123',
        ]);

        Storage::disk('public')->put("tattoos/{$tattoo->id}/video.mp4", 'contenido');
        $owner->uploads()->create([
            'path' => "tattoos/{$tattoo->id}/video.mp4",
            'disk' => 'public',
            'bytes' => 9,
            'type' => "tattoo:{$tattoo->id}",
        ]);

        return [$owner, $tattoo];
    }

    public function test_admin_delete_purges_files_and_storage_ledger(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin());
        [$owner, $tattoo] = $this->tattooWithUpload();

        $this->assertSame(9, $owner->storageUsage()['used_bytes']);

        Livewire::test(TattooList::class)->call('delete', $tattoo->id);

        $this->assertDatabaseMissing('tattoos', ['id' => $tattoo->id]);
        $this->assertDatabaseMissing('uploads', ['type' => "tattoo:{$tattoo->id}"]);
        Storage::disk('public')->assertMissing("tattoos/{$tattoo->id}/video.mp4");
        $this->assertSame(0, $owner->fresh()->storageUsage()['used_bytes']);
    }

    public function test_api_destroy_purges_files_and_storage_ledger(): void
    {
        Storage::fake('public');
        [$owner, $tattoo] = $this->tattooWithUpload();

        $this->actingAs($owner, 'sanctum')
            ->deleteJson("/api/v1/tattoos/{$tattoo->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('tattoos', ['id' => $tattoo->id]);
        $this->assertDatabaseMissing('uploads', ['type' => "tattoo:{$tattoo->id}"]);
        Storage::disk('public')->assertMissing("tattoos/{$tattoo->id}/video.mp4");
    }

    public function test_delete_resets_page_when_last_item_of_page_is_removed(): void
    {
        $this->actingAs($this->admin());
        $owner = User::factory()->create();

        $oldest = null;
        foreach (range(1, 16) as $i) {
            $t = Tattoo::create(['user_id' => $owner->id, 'name' => "Tatuaje {$i}"]);
            $t->forceFill(['created_at' => now()->subMinutes(100 - $i)])->save();
            $oldest ??= $t;
        }

        Livewire::test(TattooList::class)
            ->call('gotoPage', 2)
            ->call('delete', $oldest->id)
            ->assertSet('paginators.page', 1)
            ->assertDontSee('No se encontraron tatuajes.');
    }

    public function test_activate_content_keeps_exactly_one_active_version(): void
    {
        $this->actingAs($this->admin());
        $owner = User::factory()->create();
        $tattoo = Tattoo::create(['user_id' => $owner->id, 'name' => 'Versionado', 'short_code' => 'VER123']);
        $v1 = TattooContent::create(['tattoo_id' => $tattoo->id, 'type' => 'link', 'payload' => ['url' => 'https://a.test'], 'is_active' => true, 'order' => 0]);
        $v2 = TattooContent::create(['tattoo_id' => $tattoo->id, 'type' => 'link', 'payload' => ['url' => 'https://b.test'], 'is_active' => false, 'order' => 1]);

        Livewire::test(TattooList::class)->call('activateContent', $v2->id);

        $this->assertSame(
            1,
            TattooContent::where('tattoo_id', $tattoo->id)->where('is_active', true)->count()
        );
        $this->assertTrue($v2->fresh()->is_active);
        $this->assertFalse($v1->fresh()->is_active);
    }
}
