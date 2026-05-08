<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
use App\Models\Role;
use App\Models\Permission;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create Roles
        $roles = [
            [
                'name' => 'super_admin',
                'display_name' => 'Super Admin',
                'description' => 'Full system access - can do everything',
                'level' => 100,
            ],
            [
                'name' => 'administrator',
                'display_name' => 'Administrator',
                'description' => 'High-level management - can manage users, products, orders',
                'level' => 80,
            ],
            [
                'name' => 'finance_manager',
                'display_name' => 'Finance Manager',
                'description' => 'Financial operations specialist - manages payouts, refunds, reports',
                'level' => 70,
            ],
            [
                'name' => 'operations_manager',
                'display_name' => 'Operations Manager',
                'description' => 'Logistics & delivery specialist - manages deliveries and riders',
                'level' => 70,
            ],
            [
                'name' => 'content_manager',
                'display_name' => 'Content Manager',
                'description' => 'Product & content specialist - manages products, categories, content',
                'level' => 60,
            ],
            [
                'name' => 'support_agent',
                'display_name' => 'Support Agent',
                'description' => 'Customer service specialist - handles support tickets',
                'level' => 50,
            ],
            [
                'name' => 'analyst',
                'display_name' => 'Analyst',
                'description' => 'Data & reporting specialist - views analytics and generates reports',
                'level' => 50,
            ],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
        }

        // Create Permissions
        $permissions = [
            // Dashboard
            ['name' => 'dashboard.view', 'display_name' => 'View Dashboard', 'group' => 'dashboard'],
            ['name' => 'dashboard.analytics', 'display_name' => 'View Analytics', 'group' => 'dashboard'],

            // Users
            ['name' => 'users.view', 'display_name' => 'View Users', 'group' => 'users'],
            ['name' => 'users.create', 'display_name' => 'Create Users', 'group' => 'users'],
            ['name' => 'users.edit', 'display_name' => 'Edit Users', 'group' => 'users'],
            ['name' => 'users.delete', 'display_name' => 'Delete Users', 'group' => 'users'],
            ['name' => 'users.block', 'display_name' => 'Block/Unblock Users', 'group' => 'users'],

            // Sellers
            ['name' => 'sellers.view', 'display_name' => 'View Sellers', 'group' => 'sellers'],
            ['name' => 'sellers.approve', 'display_name' => 'Approve Sellers', 'group' => 'sellers'],
            ['name' => 'sellers.edit', 'display_name' => 'Edit Sellers', 'group' => 'sellers'],
            ['name' => 'sellers.suspend', 'display_name' => 'Suspend Sellers', 'group' => 'sellers'],
            ['name' => 'sellers.commission', 'display_name' => 'Manage Commission', 'group' => 'sellers'],

            // Riders
            ['name' => 'riders.view', 'display_name' => 'View Riders', 'group' => 'riders'],
            ['name' => 'riders.approve', 'display_name' => 'Approve Riders', 'group' => 'riders'],
            ['name' => 'riders.edit', 'display_name' => 'Edit Riders', 'group' => 'riders'],
            ['name' => 'riders.suspend', 'display_name' => 'Suspend Riders', 'group' => 'riders'],
            ['name' => 'riders.track', 'display_name' => 'Track Riders', 'group' => 'riders'],

            // Products
            ['name' => 'products.view', 'display_name' => 'View Products', 'group' => 'products'],
            ['name' => 'products.approve', 'display_name' => 'Approve Products', 'group' => 'products'],
            ['name' => 'products.edit', 'display_name' => 'Edit Products', 'group' => 'products'],
            ['name' => 'products.delete', 'display_name' => 'Delete Products', 'group' => 'products'],
            ['name' => 'products.feature', 'display_name' => 'Feature Products', 'group' => 'products'],

            // Categories
            ['name' => 'categories.view', 'display_name' => 'View Categories', 'group' => 'categories'],
            ['name' => 'categories.manage', 'display_name' => 'Manage Categories', 'group' => 'categories'],

            // Orders
            ['name' => 'orders.view', 'display_name' => 'View Orders', 'group' => 'orders'],
            ['name' => 'orders.edit', 'display_name' => 'Edit Orders', 'group' => 'orders'],
            ['name' => 'orders.cancel', 'display_name' => 'Cancel Orders', 'group' => 'orders'],
            ['name' => 'orders.refund', 'display_name' => 'Process Refunds', 'group' => 'orders'],

            // Deliveries
            ['name' => 'deliveries.view', 'display_name' => 'View Deliveries', 'group' => 'deliveries'],
            ['name' => 'deliveries.assign', 'display_name' => 'Assign Riders', 'group' => 'deliveries'],
            ['name' => 'deliveries.track', 'display_name' => 'Track Deliveries', 'group' => 'deliveries'],
            ['name' => 'deliveries.manage', 'display_name' => 'Manage Deliveries', 'group' => 'deliveries'],

            // Finance
            ['name' => 'finance.view', 'display_name' => 'View Financial Data', 'group' => 'finance'],
            ['name' => 'payouts.view', 'display_name' => 'View Payouts', 'group' => 'finance'],
            ['name' => 'payouts.process', 'display_name' => 'Process Payouts', 'group' => 'finance'],
            ['name' => 'payouts.approve', 'display_name' => 'Approve Payouts', 'group' => 'finance'],
            ['name' => 'wallets.view', 'display_name' => 'View Wallets', 'group' => 'finance'],
            ['name' => 'wallets.adjust', 'display_name' => 'Adjust Wallets', 'group' => 'finance'],
            ['name' => 'reports.financial', 'display_name' => 'Financial Reports', 'group' => 'finance'],

            // Analytics
            ['name' => 'analytics.view', 'display_name' => 'View Analytics', 'group' => 'analytics'],
            ['name' => 'reports.generate', 'display_name' => 'Generate Reports', 'group' => 'analytics'],
            ['name' => 'reports.export', 'display_name' => 'Export Reports', 'group' => 'analytics'],

            // Settings
            ['name' => 'settings.view', 'display_name' => 'View Settings', 'group' => 'settings'],
            ['name' => 'settings.edit', 'display_name' => 'Edit Settings', 'group' => 'settings'],
            ['name' => 'settings.system', 'display_name' => 'System Settings', 'group' => 'settings'],

            // Support
            ['name' => 'support.view', 'display_name' => 'View Support Tickets', 'group' => 'support'],
            ['name' => 'support.respond', 'display_name' => 'Respond to Tickets', 'group' => 'support'],
            ['name' => 'support.manage', 'display_name' => 'Manage Tickets', 'group' => 'support'],

            // Logs
            ['name' => 'logs.view', 'display_name' => 'View Logs', 'group' => 'logs'],
            ['name' => 'logs.export', 'display_name' => 'Export Logs', 'group' => 'logs'],
        ];

        foreach ($permissions as $permData) {
            Permission::firstOrCreate(
                ['name' => $permData['name']],
                $permData
            );
        }

        // Assign Permissions to Roles
        $this->assignPermissionsToRoles();

        // Create Default Super Admin
        $superAdminRole = Role::where('name', 'super_admin')->first();
        
        Admin::firstOrCreate(
            ['email' => 'admin@genesishub.com'],
            [
                'role_id' => $superAdminRole->id,
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );

        echo "✅ Admin seeder completed successfully!\n";
        echo "📧 Login: admin@genesishub.com\n";
        echo "🔑 Password: password\n";
    }

    private function assignPermissionsToRoles(): void
    {
        // Get all roles
        $superAdmin = Role::where('name', 'super_admin')->first();
        $administrator = Role::where('name', 'administrator')->first();
        $financeManager = Role::where('name', 'finance_manager')->first();
        $operationsManager = Role::where('name', 'operations_manager')->first();
        $contentManager = Role::where('name', 'content_manager')->first();
        $supportAgent = Role::where('name', 'support_agent')->first();
        $analyst = Role::where('name', 'analyst')->first();

        // Super Admin gets ALL permissions (no need to assign, handled in model)

        // Administrator
        $administrator->syncPermissions(
            Permission::whereIn('group', [
                'dashboard', 'users', 'sellers', 'riders', 
                'products', 'categories', 'orders', 'deliveries', 'support'
            ])->where('name', 'not like', '%.delete')
            ->where('name', '!=', 'settings.system')
           ->pluck('id')->toArray()
        );

        // Finance Manager
        $financeManager->syncPermissions(
            Permission::whereIn('group', ['dashboard', 'finance', 'analytics'])
                ->orWhereIn('name', [
                    'orders.view', 'orders.refund',
                    'sellers.view', 'users.view'
                ])
               ->pluck('id')->toArray()
        );

        // Operations Manager
        $operationsManager->syncPermissions(
            Permission::whereIn('group', ['dashboard', 'deliveries', 'riders'])
                ->orWhereIn('name', [
                    'orders.view', 'sellers.view', 'users.view'
                ])
               ->pluck('id')->toArray()
        );

        // Content Manager
        $contentManager->syncPermissions(
            Permission::whereIn('group', ['dashboard', 'products', 'categories'])
                ->orWhereIn('name', [
                    'sellers.view', 'orders.view'
                ])
               ->pluck('id')->toArray()
        );

        // Support Agent
        $supportAgent->syncPermissions(
            Permission::whereIn('name', [
                'dashboard.view',
                'users.view', 'sellers.view', 'orders.view',
                'support.view', 'support.respond'
            ])->pluck('id')->toArray()
        );

        // Analyst
        $analyst->syncPermissions(
            Permission::whereIn('group', ['dashboard', 'analytics'])
                ->orWhere('name', 'like', '%.view')
               ->pluck('id')->toArray()
        );
    }
}