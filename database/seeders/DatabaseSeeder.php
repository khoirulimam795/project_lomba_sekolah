<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleAndPermissionSeeder::class,   // ← WAJIB paling awal
            AdminUserSeeder::class,           // ← baru admin (butuh role admin)
        ]);    
    }
}