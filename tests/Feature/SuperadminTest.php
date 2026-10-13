<?php

namespace Tests\Feature;

use App\Models\Presentation;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SuperadminTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Authenticate superadmin user and populate superadmin session.
     */
    protected function authenticateSuperadmin(User $superadmin): static
    {
        return $this->actingAs($superadmin)->withSession([
            'superadmin_logged_in' => true,
            'superadmin_user_id' => $superadmin->id,
        ]);
    }

    /**
     * Unauthenticated guest visiting /superadmin is redirected to /superadmin/login.
     */
    public function test_guest_is_redirected_to_superadmin_login(): void
    {
        $response = $this->get('/superadmin');

        $response->assertRedirect('/login');
    }

    /**
     * Regular logged-in user without superadmin session is redirected to /superadmin/login.
     */
    public function test_regular_user_is_redirected_to_superadmin_login(): void
    {
        $user = User::factory()->create(['is_superadmin' => false]);

        $response = $this->actingAs($user)->get('/superadmin');

        $response->assertRedirect(route('superadmin.login'));
        $response->assertSessionHas('error');
    }

    /**
     * Superadmin login page renders successfully.
     */
    public function test_superadmin_login_page_renders_successfully(): void
    {
        $response = $this->get('/superadmin/login');

        $response->assertStatus(200);
        $response->assertSee('Autentikasi Superadmin');
    }

    /**
     * Superadmin can log in through the superadmin login form.
     */
    public function test_superadmin_can_login_via_superadmin_login_form(): void
    {
        $superadmin = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => Hash::make('adminsecret'),
            'is_superadmin' => true,
        ]);

        $response = $this->post('/superadmin/login', [
            'email' => 'admin@test.com',
            'password' => 'adminsecret',
        ]);

        $response->assertRedirect(route('superadmin.index'));
        $response->assertSessionHas('superadmin_logged_in', true);
        $response->assertSessionHas('superadmin_user_id', $superadmin->id);
    }

    /**
     * Non-superadmin credentials cannot log in through superadmin login form.
     */
    public function test_non_superadmin_cannot_login_through_superadmin_login_form(): void
    {
        User::factory()->create([
            'email' => 'regular@test.com',
            'password' => Hash::make('password123'),
            'is_superadmin' => false,
        ]);

        $response = $this->post('/superadmin/login', [
            'email' => 'regular@test.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHas('error');
        $this->assertNull(session('superadmin_logged_in'));
    }

    /**
     * Superadmin can log out from superadmin session.
     */
    public function test_superadmin_can_logout_from_superadmin_session(): void
    {
        $superadmin = User::factory()->create(['is_superadmin' => true]);

        $response = $this->authenticateSuperadmin($superadmin)->post('/superadmin/logout');

        $response->assertRedirect(route('superadmin.login'));
        $response->assertSessionMissing('superadmin_logged_in');
    }

    /**
     * Superadmin can access superadmin control center when session is active.
     */
    public function test_superadmin_can_view_superadmin_dashboard(): void
    {
        $superadmin = User::factory()->create(['is_superadmin' => true]);

        $response = $this->authenticateSuperadmin($superadmin)->get('/superadmin');

        $response->assertStatus(200);
        $response->assertSee('Control Center');
        $response->assertSee('Kelola Akun Pengguna');
        $response->assertSee('Kelola Fitur Sistem');
    }

    /**
     * Superadmin can create a new user account.
     */
    public function test_superadmin_can_create_new_user(): void
    {
        $superadmin = User::factory()->create(['is_superadmin' => true]);

        $response = $this->authenticateSuperadmin($superadmin)->post('/superadmin/users', [
            'name' => 'Akun Baru Test',
            'email' => 'akunbaru@test.com',
            'password' => 'secret123',
            'is_superadmin' => false,
        ]);

        $response->assertRedirect(route('superadmin.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'akunbaru@test.com',
            'is_superadmin' => false,
        ]);
    }

    /**
     * Superadmin can reset password with auto-generated secure password.
     */
    public function test_superadmin_can_reset_password_with_auto_generation(): void
    {
        $superadmin = User::factory()->create(['is_superadmin' => true]);
        $targetUser = User::factory()->create(['password' => Hash::make('old_password')]);

        $response = $this->authenticateSuperadmin($superadmin)->postJson("/superadmin/users/{$targetUser->id}/reset-password", [
            'auto_generate' => true,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertNotNull($response->json('new_password'));

        $targetUser->refresh();
        $this->assertTrue(Hash::check($response->json('new_password'), $targetUser->password));
    }

    /**
     * Superadmin can reset password with a specified password.
     */
    public function test_superadmin_can_reset_password_with_custom_password(): void
    {
        $superadmin = User::factory()->create(['is_superadmin' => true]);
        $targetUser = User::factory()->create();

        $response = $this->authenticateSuperadmin($superadmin)->postJson("/superadmin/users/{$targetUser->id}/reset-password", [
            'auto_generate' => false,
            'password' => 'CustomPass123!',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $targetUser->refresh();
        $this->assertTrue(Hash::check('CustomPass123!', $targetUser->password));
    }

    /**
     * Superadmin can promote or demote other users.
     */
    public function test_superadmin_can_toggle_user_role(): void
    {
        $superadmin = User::factory()->create(['is_superadmin' => true]);
        $targetUser = User::factory()->create(['is_superadmin' => false]);

        $response = $this->authenticateSuperadmin($superadmin)->postJson("/superadmin/users/{$targetUser->id}/toggle-role");

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'is_superadmin' => true]);

        $this->assertTrue($targetUser->fresh()->is_superadmin);
    }

    /**
     * Superadmin cannot demote or delete themselves.
     */
    public function test_superadmin_cannot_demote_or_delete_themselves(): void
    {
        $superadmin = User::factory()->create(['is_superadmin' => true]);

        // Try toggle role self
        $toggleResponse = $this->authenticateSuperadmin($superadmin)->postJson("/superadmin/users/{$superadmin->id}/toggle-role");
        $toggleResponse->assertStatus(422);

        // Try delete self
        $deleteResponse = $this->authenticateSuperadmin($superadmin)->deleteJson("/superadmin/users/{$superadmin->id}");
        $deleteResponse->assertStatus(422);

        $this->assertDatabaseHas('users', ['id' => $superadmin->id]);
    }

    /**
     * Superadmin can delete other user and cascade presentations.
     */
    public function test_superadmin_can_delete_user_and_cascade_presentations(): void
    {
        $superadmin = User::factory()->create(['is_superadmin' => true]);
        $targetUser = User::factory()->create();

        $presentation = Presentation::create([
            'user_id' => $targetUser->id,
            'title' => 'Presentasi Target',
            'access_code' => '55667788',
            'theme' => 'default',
        ]);

        $response = $this->authenticateSuperadmin($superadmin)->delete("/superadmin/users/{$targetUser->id}");

        $response->assertRedirect(route('superadmin.index'));
        $this->assertDatabaseMissing('users', ['id' => $targetUser->id]);
        $this->assertDatabaseMissing('presentations', ['id' => $presentation->id]);
    }

    /**
     * Superadmin can toggle system feature flags.
     */
    public function test_superadmin_can_toggle_feature_flags(): void
    {
        $superadmin = User::factory()->create(['is_superadmin' => true]);
        SystemSetting::seedDefaultFeatures();

        $setting = SystemSetting::where('key', 'feature_registration')->firstOrFail();
        $this->assertTrue($setting->is_enabled);

        $response = $this->authenticateSuperadmin($superadmin)->postJson("/superadmin/features/{$setting->id}/toggle");

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'is_enabled' => false]);

        $this->assertFalse($setting->fresh()->is_enabled);
    }
}
