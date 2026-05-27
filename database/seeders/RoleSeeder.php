<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $resources = [
            'products', 'spends', 'leads', 'orders', 'sourcing',
            'stocks', 'warehouses', 'expenses', 'suppliers',
            'users', 'teams', 'countries', 'currencies', 'platforms',
            'ad_accounts', 'settings', 'analytics', 'profit',
        ];

        $abilities = ['view_any', 'view', 'create', 'update', 'delete'];

        $permissions = [];
        foreach ($resources as $resource) {
            foreach ($abilities as $ability) {
                $permissions[] = "{$ability}_{$resource}";
            }
        }
        $permissions[] = 'access_admin_panel';
        $permissions[] = 'export_data';
        $permissions[] = 'approve_sourcing';

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $roleMatrix = [
            'admin' => $permissions,
            'manager' => array_filter($permissions, fn ($p) => ! str_contains($p, 'delete_users') && ! str_contains($p, 'delete_settings')),
            'media_buyer' => [
                'access_admin_panel', 'export_data',
                'view_any_spends', 'view_spends', 'create_spends', 'update_spends',
                'view_any_products', 'view_products',
                'view_any_ad_accounts', 'view_ad_accounts',
                'view_any_analytics', 'view_analytics',
                'view_any_profit', 'view_profit',
            ],
            'agent' => [
                'access_admin_panel',
                'view_any_leads', 'view_leads', 'update_leads',
                'view_any_orders', 'view_orders', 'create_orders', 'update_orders',
                'view_any_products', 'view_products',
            ],
            'sourcing_manager' => [
                'access_admin_panel', 'approve_sourcing',
                'view_any_sourcing', 'view_sourcing', 'create_sourcing', 'update_sourcing', 'delete_sourcing',
                'view_any_suppliers', 'view_suppliers', 'create_suppliers', 'update_suppliers',
                'view_any_products', 'view_products', 'create_products', 'update_products',
            ],
            'accountant' => [
                'access_admin_panel', 'export_data',
                'view_any_expenses', 'view_expenses', 'create_expenses', 'update_expenses', 'delete_expenses',
                'view_any_orders', 'view_orders',
                'view_any_spends', 'view_spends',
                'view_any_analytics', 'view_analytics',
                'view_any_profit', 'view_profit',
            ],
        ];

        foreach ($roleMatrix as $name => $perms) {
            $role = Role::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
            $role->syncPermissions(array_values($perms));
        }
    }
}
