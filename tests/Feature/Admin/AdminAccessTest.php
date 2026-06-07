<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    /** Todas las páginas del panel admin. @return list<string> */
    private function adminRoutes(): array
    {
        return [
            'admin.dashboard',
            'admin.tattoos', 'admin.qr-generator', 'admin.scans', 'admin.link-pages', 'admin.tatuadores',
            'admin.users', 'admin.companies', 'admin.team-members', 'admin.storage-usage',
            'admin.pricing', 'admin.storage-packs', 'admin.subscriptions', 'admin.referrals', 'admin.billing-alerts',
            'admin.activity-log', 'admin.api-tokens', 'admin.account',
        ];
    }

    public function test_admin_can_open_every_admin_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        foreach ($this->adminRoutes() as $name) {
            $this->actingAs($admin)
                ->get(route($name))
                ->assertOk();
        }
    }

    public function test_non_admin_is_forbidden_from_every_admin_page(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        foreach ($this->adminRoutes() as $name) {
            $this->actingAs($user)
                ->get(route($name))
                ->assertForbidden();
        }
    }

    public function test_guest_is_redirected_to_login(): void
    {
        foreach ($this->adminRoutes() as $name) {
            $this->get(route($name))->assertRedirect(route('login'));
        }
    }
}
