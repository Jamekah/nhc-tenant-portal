<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Seed the roles and permissions for the NHC Portal.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all permissions
        $permissions = [
            // Super Admin permissions
            'manage_admins',
            'view_audit_logs',
            'configure_system',
            'manage_regions',

            // Admin permissions
            'manage_property_types',
            'manage_tenants',
            'bulk_import_tenants',
            'manage_invoices',
            'generate_invoices',
            'record_payments',
            'view_all_tenants',
            'change_tenant_status',
            'manage_support_tickets',

            // Client permissions
            'view_own_dashboard',
            'make_online_payments',
            'download_statements',
            'submit_tickets',
            'view_own_payments',
        ];

        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create Super Admin role with ALL permissions
        $superAdmin = Role::create(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Create Admin role with operational permissions
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo([
            'manage_property_types',
            'manage_tenants',
            'bulk_import_tenants',
            'manage_invoices',
            'generate_invoices',
            'record_payments',
            'view_all_tenants',
            'change_tenant_status',
            'manage_support_tickets',
            'view_own_dashboard',
        ]);

        // Create Client role with tenant permissions
        $client = Role::create(['name' => 'client']);
        $client->givePermissionTo([
            'view_own_dashboard',
            'make_online_payments',
            'download_statements',
            'submit_tickets',
            'view_own_payments',
        ]);
    }
}
