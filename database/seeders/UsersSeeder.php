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
                'role' => 'super_admin',
            ],
            [
                'name' => 'Admin User',
                'email' => 'admin@pos.com',
                'role' => 'admin',
            ],
            [
                'name' => 'Cashier User',
                'email' => 'cashier@pos.com',
                'role' => 'cashier',
            ],
            [
                'name' => 'Supplier User',
                'email' => 'supplier@pos.com',
                'role' => 'supplier',
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
                ]
            );

            // 2️⃣ Assign role
            $roleId = DB::table('roles')->where('name', $data['role'])->value('id');
            if ($roleId) {
                DB::table('role_user')->updateOrInsert(
                    ['user_id' => $user->id, 'role_id' => $roleId]
                );
            }

            // 3️⃣ Optional: create staff or supplier profile
            if (in_array($data['role'], ['super_admin', 'admin', 'cashier'])) {
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
            } elseif ($data['role'] === 'supplier') {
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

