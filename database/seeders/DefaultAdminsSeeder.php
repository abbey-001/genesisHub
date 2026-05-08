<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
use App\Models\Role;

class DefaultAdminsSeeder extends Seeder
{
    public function run(): void
    {
        $admins = [
            [
                'role' => 'administrator',
                'name' => 'Administrator',
                'email' => 'admin@test.com',
            ],
            [
                'role' => 'finance_manager',
                'name' => 'Finance Manager',
                'email' => 'finance@test.com',
            ],
            [
                'role' => 'operations_manager',
                'name' => 'Operations Manager',
                'email' => 'operations@test.com',
            ],
            [
                'role' => 'content_manager',
                'name' => 'Content Manager',
                'email' => 'content@test.com',
            ],
            [
                'role' => 'support_agent',
                'name' => 'Support Agent',
                'email' => 'support@test.com',
            ],
            [
                'role' => 'analyst',
                'name' => 'Analyst',
                'email' => 'analyst@test.com',
            ],
        ];

        foreach ($admins as $data) {
            $role = Role::where('name', $data['role'])->first();

            if (!$role) {
                $this->command->warn("Role '{$data['role']}' not found. Skipping {$data['name']}.");
                continue;
            }

            Admin::firstOrCreate(
                ['email' => $data['email']],
                [
                    'role_id' => $role->id,
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'is_active' => true,
                ]
            );

            $this->command->info("✅ Created admin: {$data['name']} ({$data['role']})");
        }
    }
}
