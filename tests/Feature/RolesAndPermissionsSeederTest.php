<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolesAndPermissionsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeding_can_be_repeated_without_creating_accounts_or_duplicates(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('roles', 5);
        $this->assertDatabaseCount('permissions', 1);
        $this->assertDatabaseCount('role_has_permissions', 3);
        $this->assertDatabaseCount('users', 0);
        foreach (['Super Admin', 'League Admin', 'Team Manager', 'Scorekeeper', 'User'] as $name) {
            $this->assertDatabaseHas('roles', ['name' => $name, 'guard_name' => 'web']);
        }
    }

    public function test_reseeding_preserves_existing_assignments_and_additional_permissions(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('League Admin');
        $permission = Permission::findOrCreate('custom ability', 'web');
        Role::findByName('League Admin', 'web')->givePermissionTo($permission);

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->assertTrue($user->fresh()->hasRole('League Admin'));
        $this->assertTrue($user->fresh()->can('custom ability'));
        $this->assertTrue($user->fresh()->can('access admin'));
    }
}
