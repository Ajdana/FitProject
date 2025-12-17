<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            // Profile - own
            'profile.create.own',
            'profile.read.own',
            'profile.update.own',
            'profile.delete.own',

            // Profile - all (admin)
            'profile.read.all',
            'profile.update.all',
            'profile.delete.all',

            // Recipes - own
            'recipe.create',
            'recipe.read.own',
            'recipe.update.own',
            'recipe.delete.own',

            // Recipes - all
            'recipe.read.all',
            'recipe.update.all',
            'recipe.delete.all',

            // Scan history
            'scan.read.own',
            'scan.read.all',
            'scan.delete.all',
        ];


        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $user = Role::firstOrCreate(['name' => 'user']);
        $admin = Role::firstOrCreate(['name' => 'admin']);

        // USER
        $user->givePermissionTo([
            'profile.create.own',
            'profile.read.own',
            'profile.update.own',
            'profile.delete.own',

            'recipe.create',
            'recipe.read.own',
            'recipe.update.own',
            'recipe.delete.own',

            'scan.read.own',
        ]);

        // ADMIN
        $admin->givePermissionTo([
            'profile.create.own',
            'profile.read.own',
            'profile.update.own',
            'profile.delete.own',

            'profile.read.all',
            'profile.update.all',
            'profile.delete.all',

            'recipe.create',
            'recipe.read.own',
            'recipe.update.own',
            'recipe.delete.own',

            'recipe.read.all',
            'recipe.update.all',
            'recipe.delete.all',

            'scan.read.own',
            'scan.read.all',
            'scan.delete.all',
        ]);
    }
}
