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

        $permissionRegistrar->forgetCachedPermissions();

        foreach (['Super Admin', 'League Admin', 'Team Manager', 'Scorekeeper', 'User'] as $roleName) {
            $role = Role::findOrCreate($roleName, 'web');

            if (in_array($roleName, ['League Admin', 'Team Manager', 'Scorekeeper'], true)) {
                $role->givePermissionTo($permission);
            }
        }

        $permissionRegistrar->forgetCachedPermissions();
    }
}
