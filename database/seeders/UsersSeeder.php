<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $password = env('DEFAULT_SEEDER_PASSWORD', 'password123');

        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@pos.com',
                'role' => 0,
            ],
            [
                'name' => 'Admin User',
                'email' => 'admin@pos.com',
                'role' => 1,
            ],
            [
                'name' => 'Cashier User',
                'email' => 'cashier@pos.com',
                'role' => 2,
            ],
            [
                'name' => 'Supplier User',
                'email' => 'supplier@pos.com',
                'role' => 3,
            ],
        ];

        foreach ($users as $data) {
            // 1️⃣ Create or update user
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'uuid' => Str::uuid(),
                    'name' => $data['name'],
                    'password' => $password,
                    'status' => 1,
                    'email_verified_at' => now(),
                    'role' => $data['role'],
                ]
            );

            // 3️⃣ Optional: create staff or supplier profile
            if (in_array($data['role'], [0, 1, 2])) {
                DB::table('staff_profiles')->updateOrInsert(
                    ['user_id' => $user->id],
                    [
                        'staff_code' => strtoupper(substr($data['name'], 0, 3)) . rand(100, 999),
                        'position' => ucfirst($data['role']),
                        'hired_at' => now(),
                        'branch_id' => 1, // assumes branch 1 exists
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            } elseif ($data['role'] === 2) {
                DB::table('supplier_profiles')->updateOrInsert(
                    ['user_id' => $user->id],
                    [
                        'company_name' => $data['name'] . ' Ltd',
                        'payment_terms' => 'net_30',
                        'tax_id' => 'TAX' . rand(10000, 99999),
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}

