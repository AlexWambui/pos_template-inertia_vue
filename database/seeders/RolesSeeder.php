<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'super_admin',
            'admin',
            'cashier',
            'customer',
            'supplier',
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
    ['name' => $role],
        [
                    'created_at' => now(), 
                    'updated_at' => now(),
                ]
            );
        }
    }
}
