<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // reset cache dulu biar nggak stale
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Role::firstOrCreate(['name' => 'admin',            'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'juri',             'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'operator-sekolah', 'guard_name' => 'web']);
    }
}