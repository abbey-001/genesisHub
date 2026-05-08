<?php

namespace Database\Seeders;

use App\Models\DeliveryCompany;
use App\Models\User;
use App\Models\DeliveryCompanyUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DeliveryCompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test delivery companies
        $companies = [
            [
                'company_name' => 'Swift Delivery Services',
                'contact_person' => 'John Doe',
                'email' => 'swift@delivery.com',
                'phone_number' => '08012345678',
                'address' => '123 Main Street, Abuja, Nigeria',
                'business_registration' => 'RC-123456',
                'is_verified' => true,
                'is_active' => true,
                'rating' => 4.8,
                'completed_deliveries' => 150,
                'failed_deliveries' => 5,
                'bank_name' => 'GTBank',
                'account_number' => '0123456789',
                'account_name' => 'Swift Delivery Services',
            ],
            [
                'company_name' => 'Express Logistics Ltd',
                'contact_person' => 'Jane Smith',
                'email' => 'express@logistics.com',
                'phone_number' => '08087654321',
                'address' => '456 Business Avenue, Lagos, Nigeria',
                'business_registration' => 'RC-789012',
                'is_verified' => true,
                'is_active' => true,
                'rating' => 4.5,
                'completed_deliveries' => 200,
                'failed_deliveries' => 10,
                'bank_name' => 'Access Bank',
                'account_number' => '9876543210',
                'account_name' => 'Express Logistics Limited',
            ],
            [
                'company_name' => 'QuickShip Couriers',
                'contact_person' => 'Ahmed Ibrahim',
                'email' => 'quickship@couriers.com',
                'phone_number' => '08098765432',
                'address' => '789 Industrial Road, Kano, Nigeria',
                'business_registration' => 'RC-345678',
                'is_verified' => false,
                'is_active' => false,
                'rating' => 5.0,
                'completed_deliveries' => 0,
                'failed_deliveries' => 0,
                'bank_name' => 'UBA',
                'account_number' => '1234567890',
                'account_name' => 'QuickShip Couriers',
            ],
        ];

        foreach ($companies as $companyData) {
            // Create the company
            $company = DeliveryCompany::create($companyData);

            // Create an admin user for this company
            $user = User::create([
                'name' => $companyData['contact_person'],
                'email' => $companyData['email'],
                'password' => Hash::make('password'), // Default password for testing
                'user_type' => 'delivery_company',
            ]);

            // Link user to company as admin
            DeliveryCompanyUser::create([
                'delivery_company_id' => $company->id,
                'user_id' => $user->id,
                'role' => 'admin',
                'is_active' => true,
            ]);

            // Create an additional operator user for first two companies
            if ($company->is_verified) {
                $operatorUser = User::create([
                    'name' => $companyData['contact_person'] . ' - Operator',
                    'email' => str_replace('@', '+operator@', $companyData['email']),
                    'password' => Hash::make('password'),
                    'user_type' => 'delivery_company',
                ]);

                DeliveryCompanyUser::create([
                    'delivery_company_id' => $company->id,
                    'user_id' => $operatorUser->id,
                    'role' => 'operator',
                    'is_active' => true,
                ]);
            }
        }

        $this->command->info('Created ' . count($companies) . ' delivery companies with users');
    }
}