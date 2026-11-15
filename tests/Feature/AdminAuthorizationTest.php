<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class AdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    #[TestWith(['Super Admin', true])]
    #[TestWith(['League Admin', true])]
    #[TestWith(['Team Manager', true])]
    #[TestWith(['Scorekeeper', true])]
    #[TestWith(['User', false])]
    public function test_roles_receive_the_expected_admin_permission(string $role, bool $allowed): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole($role);

        $this->assertSame($allowed, $user->can('access admin'));
    }

    #[TestWith(['Super Admin'])]
    #[TestWith(['League Admin'])]
    #[TestWith(['Team Manager'])]
    #[TestWith(['Scorekeeper'])]
    public function test_authorized_users_can_render_the_admin_dashboard(string $role): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole($role);

        $this->actingAs($user)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Dashboard')
                ->where('auth.canAccessAdmin', true));
    }

    public function test_users_are_forbidden_from_the_admin_dashboard(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('User');

        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_existing_users_without_roles_cannot_access_admin_but_keep_dashboard_access(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
        $this->get(route('dashboard'))->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('auth.canAccessAdmin', false));
    }

    public function test_super_admin_can_authorize_abilities_without_explicit_grants(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        $this->assertTrue($user->can('a future ability'));
    }

    public function test_non_super_admin_cannot_authorize_unknown_abilities(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('League Admin');

        $this->assertFalse($user->can('a future ability'));
    }

    public function test_revoking_admin_permission_removes_dashboard_access(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Scorekeeper');
        $user->roles->first()->revokePermissionTo('access admin');

        $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_admin_login_returns_to_the_requested_admin_dashboard(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Super Admin');
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->get(route('admin.dashboard'))->assertOk();
    }

    public function test_admin_dashboard_named_route_resolves_to_the_admin_prefix(): void
    {
        $this->assertSame('/admin', route('admin.dashboard', absolute: false));
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }
}
