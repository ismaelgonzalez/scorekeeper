<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class AuthenticatedLayoutTest extends TestCase
{
    use RefreshDatabase;

    #[TestWith(['User', 'dashboard', 'Dashboard', false])]
    #[TestWith(['User', 'profile.edit', 'Profile/Edit', false])]
    #[TestWith(['Super Admin', 'dashboard', 'Dashboard', true])]
    #[TestWith(['Super Admin', 'profile.edit', 'Profile/Edit', true])]
    public function test_shared_pages_keep_admin_navigation_restricted_by_permission(string $role, string $routeName, string $component, bool $canAccessAdmin): void
    {
        $this->withoutVite();
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole($role);

        $this->actingAs($user)->get(route($routeName))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component($component)
                ->where('auth.user.id', $user->id)
                ->where('auth.canAccessAdmin', $canAccessAdmin)
                ->has('adminNavigation', $canAccessAdmin ? 8 : 0));

        $this->assertSame([$role], $user->fresh()->getRoleNames()->all());
    }
}
