<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = env('DEFAULT_SEEDER_PASSWORD', 'password123');
        $hashed_password = Hash::make($password);

        $users_data = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@pos.com',
                'role' => 0
            ],
            [
                'name' => 'Admin User',
                'email' => 'admin@pos.com',
                'role' => 1
            ],
            [
                'name' => 'Cashier User',
                'email' => 'cashier@pos.com',
                'role' => 2
            ],
            [
                'name' => 'Supplier User',
                'email' => 'supplier@pos.com',
                'role' => 3
            ],
        ];

        foreach ($users_data as $user_data) {
            User::updateOrCreate(
                [
                    'email' => $user_data['email'],
                ],
                [
                    'name' => $user_data['name'],
                    'password' => $hashed_password,
                    'role' => $user_data['role'],
                    'status' => 1,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
