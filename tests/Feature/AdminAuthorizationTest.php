<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
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

    public function test_admin_navigation_marks_unimplemented_sections_as_unavailable(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('League Admin');

        $this->actingAs($user)->get(route('admin.dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->has('adminNavigation', 8)
                ->where('adminNavigation.0.route', 'admin.dashboard')
                ->where('adminNavigation.1', ['key' => 'leagues', 'label' => 'Leagues', 'route' => 'admin.leagues.index'])
                ->where('adminNavigation.7', ['key' => 'settings', 'label' => 'Settings', 'route' => null])
                ->where('appVersion', null));
    }

    public function test_non_admin_users_receive_no_admin_navigation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page->where('adminNavigation', []));
    }

    #[TestWith([false])]
    #[TestWith([true])]
    public function test_available_navigation_is_filtered_by_server_authorization(bool $allowed): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('League Admin');
        Route::get('/admin/teams', fn (): string => 'Teams')
            ->middleware(['web', 'auth', 'can:access admin', 'can:view teams'])
            ->name('admin.teams.index');
        Route::getRoutes()->refreshNameLookups();
        Gate::define('view teams', fn (User $user): bool => $allowed);

        $this->actingAs($user)->get(route('admin.dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('adminNavigation', function ($items) use ($allowed): bool {
                    $teams = collect($items)->firstWhere('key', 'teams');

                    return $allowed ? $teams['route'] === 'admin.teams.index' : $teams === null;
                }));

        if ($allowed) {
            $this->get('/admin/teams')->assertOk();
        } else {
            $this->get('/admin/teams')->assertForbidden();
        }
    }

    public function test_configured_application_version_is_shared_with_the_layout(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Super Admin');
        config(['app.version' => '1.2.3']);

        $this->actingAs($user)->get(route('admin.dashboard'))
            ->assertInertia(fn (Assert $page) => $page->where('appVersion', '1.2.3'));
    }
}
