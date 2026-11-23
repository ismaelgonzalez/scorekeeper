<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(PermissionRegistrar $permissionRegistrar): void
    {
        $permissionRegistrar->forgetCachedPermissions();

        $permission = Permission::findOrCreate('access admin', 'web');
        $leaguePermissions = array_map(
            fn (string $ability): Permission => Permission::findOrCreate($ability, 'web'),
            ['view leagues', 'create leagues', 'edit leagues', 'delete leagues'],
        );

        $permissionRegistrar->forgetCachedPermissions();

        foreach (['Super Admin', 'League Admin', 'Team Manager', 'Scorekeeper', 'User'] as $roleName) {
            $role = Role::findOrCreate($roleName, 'web');

            if (in_array($roleName, ['League Admin', 'Team Manager', 'Scorekeeper'], true)) {
                $role->givePermissionTo($permission);
            }

            if ($roleName === 'League Admin') {
                $role->givePermissionTo($leaguePermissions);
            }
        }

        $permissionRegistrar->forgetCachedPermissions();
    }
}
