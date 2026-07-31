<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Mail\AdminLoginAlertMail;
use App\Models\User;
use App\Services\TotpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

final class AdminTwoFactorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('login');
    }

    private function adminWith2fa(string $secret): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => ['AAAAA-BBBBB', 'CCCCC-DDDDD'],
            'two_factor_confirmed_at' => now(),
        ]);
    }

    public function test_login_with_2fa_redirects_to_challenge_without_opening_session(): void
    {
        $secret = app(TotpService::class)->generateSecret();
        $user = $this->adminWith2fa($secret);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('two-factor.challenge'));
        $this->assertGuest();
    }

    public function test_challenge_rejects_wrong_code_and_accepts_valid_totp(): void
    {
        Mail::fake();
        $totp = app(TotpService::class);
        $secret = $totp->generateSecret();
        $user = $this->adminWith2fa($secret);

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        // Código inválido
        $this->post(route('two-factor.challenge.store'), ['code' => '000000'])
            ->assertSessionHasErrors('code');
        $this->assertGuest();

        // Código válido
        $this->post(route('two-factor.challenge.store'), ['code' => $totp->currentCode($secret)])
            ->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_challenge_accepts_recovery_code_and_consumes_it(): void
    {
        Mail::fake();
        $secret = app(TotpService::class)->generateSecret();
        $user = $this->adminWith2fa($secret);

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $this->post(route('two-factor.challenge.store'), ['code' => 'AAAAA-BBBBB'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertSame(['CCCCC-DDDDD'], $user->fresh()->two_factor_recovery_codes);
    }

    public function test_admin_without_2fa_can_enroll_via_setup_and_enter_panel(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        // El setup genera secret + códigos de recuperación
        $this->get(route('two-factor.setup'))->assertOk()->assertSee('Escanea el código');
        $admin->refresh();
        $this->assertNotNull($admin->two_factor_secret);
        $this->assertCount(8, $admin->two_factor_recovery_codes);

        // Confirmar con el código TOTP activa el 2FA y abre el panel
        $code = app(TotpService::class)->currentCode((string) $admin->two_factor_secret);
        $this->post(route('two-factor.setup.confirm'), ['code' => $code])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertNotNull($admin->fresh()->two_factor_confirmed_at);
        $this->get(route('admin.dashboard'))->assertOk();
    }

    public function test_non_admin_login_is_not_sent_to_two_factor_flows(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard', absolute: false));
        $this->assertAuthenticatedAs($user);
    }

    public function test_new_ip_admin_login_sends_alert_email_once(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => 'admin']);

        // Primer login: IP nueva → alerta
        $this->post('/login', ['email' => $admin->email, 'password' => 'password']);
        Mail::assertSent(AdminLoginAlertMail::class, 1);

        // Segundo login desde la misma IP: sin alerta nueva
        $this->post('/logout');
        $this->post('/login', ['email' => $admin->email, 'password' => 'password']);
        Mail::assertSent(AdminLoginAlertMail::class, 1);
    }

    public function test_login_endpoint_is_rate_limited_per_ip(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 10) as $i) {
            $this->post('/login', ['email' => "wrong{$i}@x.test", 'password' => 'bad-pass']);
        }

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertStatus(429);
    }
}
