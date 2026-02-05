<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BranchesSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            [
                'name' => 'Main Branch',
                'code' => 'BR001',
                'phone' => '+254700000001',
                'email' => 'main@pos.com',
                'address' => '123 Main Street',
                'city' => 'Nairobi',
                'is_active' => true,
            ],
            // Add more branches here if needed
        ];

        foreach ($branches as $branch) {
            DB::table('branches')->updateOrInsert(
                ['code' => $branch['code']], // unique constraint
                array_merge($branch, [
                    'uuid' => Str::uuid(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
