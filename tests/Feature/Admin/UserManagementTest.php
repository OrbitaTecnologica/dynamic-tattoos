<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Livewire\Admin\UserList;
use App\Models\LinkPage;
use App\Models\Tattoo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

final class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    // =========================================================================
    // A1 · Alta de usuarios
    // =========================================================================

    public function test_admin_can_create_user_with_password_and_role(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(UserList::class)
            ->call('openCreate')
            ->set('userName', 'Nuevo Cliente')
            ->set('userEmail', 'nuevo@dynamic-tattoos.test')
            ->set('userPassword', 'Secret123')
            ->set('userRole', 'ambassador')
            ->call('saveUser')
            ->assertHasNoErrors()
            ->assertSet('showModal', false);

        $user = User::where('email', 'nuevo@dynamic-tattoos.test')->firstOrFail();
        $this->assertSame('ambassador', $user->role);
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue(Hash::check('Secret123', $user->password));
        $this->assertTrue(Auth::attempt(['email' => 'nuevo@dynamic-tattoos.test', 'password' => 'Secret123']));
    }

    public function test_create_requires_password(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(UserList::class)
            ->call('openCreate')
            ->set('userName', 'Sin Pass')
            ->set('userEmail', 'sinpass@dynamic-tattoos.test')
            ->set('userRole', 'user')
            ->call('saveUser')
            ->assertHasErrors(['userPassword' => 'required']);

        $this->assertDatabaseMissing('users', ['email' => 'sinpass@dynamic-tattoos.test']);
    }

    public function test_create_rejects_duplicate_email(): void
    {
        $this->actingAs($this->admin());
        $existing = User::factory()->create(['email' => 'dup@dynamic-tattoos.test']);

        Livewire::test(UserList::class)
            ->call('openCreate')
            ->set('userName', 'Duplicado')
            ->set('userEmail', 'dup@dynamic-tattoos.test')
            ->set('userPassword', 'Secret123')
            ->set('userRole', 'user')
            ->call('saveUser')
            ->assertHasErrors(['userEmail']);
    }

    // =========================================================================
    // Fix bug: editar un embajador ya no revienta la validación
    // =========================================================================

    public function test_admin_can_edit_user_to_ambassador_role(): void
    {
        $this->actingAs($this->admin());
        $user = User::factory()->create(['role' => 'user']);

        Livewire::test(UserList::class)
            ->call('openEdit', $user->id)
            ->set('userRole', 'ambassador')
            ->call('saveUser')
            ->assertHasNoErrors();

        $this->assertSame('ambassador', $user->fresh()->role);
    }

    public function test_edit_without_password_keeps_existing_password(): void
    {
        $this->actingAs($this->admin());
        $user = User::factory()->create();
        $originalHash = $user->password;

        Livewire::test(UserList::class)
            ->call('openEdit', $user->id)
            ->set('userName', 'Nombre Cambiado')
            ->call('saveUser')
            ->assertHasNoErrors();

        $fresh = $user->fresh();
        $this->assertSame('Nombre Cambiado', $fresh->name);
        $this->assertSame($originalHash, $fresh->password);
    }

    // =========================================================================
    // A3 · Papelera / soft-delete + mitigaciones del observer
    // =========================================================================

    public function test_delete_moves_user_to_trash(): void
    {
        $this->actingAs($this->admin());
        $victim = User::factory()->create();

        Livewire::test(UserList::class)->call('deleteUser', $victim->id);

        $this->assertSoftDeleted('users', ['id' => $victim->id]);
    }

    /** RIESGO #1: el email debe liberarse para no bloquear un nuevo registro. */
    public function test_trashing_frees_the_email_for_reregistration(): void
    {
        $this->actingAs($this->admin());
        $victim = User::factory()->create(['email' => 'reuse@dynamic-tattoos.test']);

        Livewire::test(UserList::class)->call('deleteUser', $victim->id);

        // El email en BD queda renombrado (no colisiona con el índice unique)...
        $this->assertDatabaseMissing('users', [
            'email' => 'reuse@dynamic-tattoos.test',
            'deleted_at' => null,
        ]);
        $this->assertStringStartsWith('trashed_', (string) $victim->fresh()->email);

        // ...y crear un usuario nuevo con ese mismo email NO revienta.
        $new = User::factory()->create(['email' => 'reuse@dynamic-tattoos.test']);
        $this->assertNotNull($new->id);
        $this->assertNotSame($victim->id, $new->id);
    }

    /** RIESGO #2: los tokens de API se revocan al enviar a papelera. */
    public function test_trashing_revokes_api_tokens(): void
    {
        $this->actingAs($this->admin());
        $victim = User::factory()->create();
        $victim->createToken('test-device');
        $this->assertSame(1, DB::table('personal_access_tokens')->where('tokenable_id', $victim->id)->count());

        Livewire::test(UserList::class)->call('deleteUser', $victim->id);

        $this->assertSame(0, DB::table('personal_access_tokens')->where('tokenable_id', $victim->id)->count());
    }

    /** RIESGO #3: el contenido público se despublica en papelera y se republica al restaurar. */
    public function test_trashing_despublishes_content_and_restore_republishes(): void
    {
        $this->actingAs($this->admin());
        $victim = User::factory()->create(['email' => 'creator@dynamic-tattoos.test']);
        LinkPage::forceCreate(['user_id' => $victim->id, 'slug' => 'creator-lp', 'is_published' => true]);
        Tattoo::forceCreate(['user_id' => $victim->id, 'short_code' => 'CRE123', 'name' => 'Demo', 'is_active' => true]);

        Livewire::test(UserList::class)->call('deleteUser', $victim->id);

        $this->assertSame(0, (int) LinkPage::where('user_id', $victim->id)->value('is_published'));
        $this->assertSame(0, (int) Tattoo::where('user_id', $victim->id)->value('is_active'));

        Livewire::test(UserList::class)->set('showTrashed', true)->call('restore', $victim->id);

        $this->assertSame(1, (int) LinkPage::where('user_id', $victim->id)->value('is_published'));
        $this->assertSame(1, (int) Tattoo::where('user_id', $victim->id)->value('is_active'));
        $this->assertSame('creator@dynamic-tattoos.test', $victim->fresh()->email);
    }

    public function test_trashed_user_cannot_authenticate(): void
    {
        $this->actingAs($this->admin());
        $victim = User::factory()->create(['email' => 'gone@dynamic-tattoos.test']);

        Livewire::test(UserList::class)->call('deleteUser', $victim->id);

        // La factory usa la password "password"; el global scope excluye al usuario en papelera.
        $this->assertFalse(Auth::attempt(['email' => 'gone@dynamic-tattoos.test', 'password' => 'password']));
    }

    public function test_restore_brings_user_back_to_active(): void
    {
        $this->actingAs($this->admin());
        $victim = User::factory()->create(['email' => 'back@dynamic-tattoos.test']);
        Livewire::test(UserList::class)->call('deleteUser', $victim->id);

        Livewire::test(UserList::class)->set('showTrashed', true)->call('restore', $victim->id);

        $this->assertDatabaseHas('users', [
            'id' => $victim->id,
            'email' => 'back@dynamic-tattoos.test',
            'deleted_at' => null,
        ]);
    }

    public function test_force_delete_removes_user_permanently(): void
    {
        $this->actingAs($this->admin());
        $victim = User::factory()->create();
        Livewire::test(UserList::class)->call('deleteUser', $victim->id);

        Livewire::test(UserList::class)->set('showTrashed', true)->call('forceDelete', $victim->id);

        $this->assertDatabaseMissing('users', ['id' => $victim->id]);
    }

    public function test_trashed_users_are_excluded_from_active_list_and_counted(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);
        $victim = User::factory()->create();
        Livewire::test(UserList::class)->call('deleteUser', $victim->id);

        $component = Livewire::test(UserList::class);
        // Vista activos por defecto: el usuario en papelera no aparece en la lista.
        $activeIds = $component->instance()->users()->pluck('id')->all();
        $this->assertNotContains($victim->id, $activeIds);
        // Contador de papelera = 1.
        $this->assertSame(1, $component->instance()->trashedCount());
    }

    // =========================================================================
    // A2 · Limpieza masiva
    // =========================================================================

    public function test_bulk_delete_sends_selected_to_trash_and_never_self(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();

        Livewire::test(UserList::class)
            ->set('selected', [(string) $admin->id, (string) $u1->id, (string) $u2->id])
            ->call('deleteSelected');

        $this->assertNull($admin->fresh()->deleted_at, 'El admin nunca debe auto-borrarse');
        $this->assertSoftDeleted('users', ['id' => $u1->id]);
        $this->assertSoftDeleted('users', ['id' => $u2->id]);
    }

    public function test_select_page_selects_everyone_except_self(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();

        $component = Livewire::test(UserList::class)->set('selectPage', true);

        $selected = $component->get('selected');
        $this->assertContains((string) $u1->id, $selected);
        $this->assertContains((string) $u2->id, $selected);
        $this->assertNotContains((string) $admin->id, $selected);
    }
}
