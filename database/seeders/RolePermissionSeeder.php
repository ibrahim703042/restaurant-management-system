<?php

namespace Database\Seeders;

use App\Models\Position;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Restaurant permissions (dot-separated groups). Assign via roles below.
     */
    public function run(): void
    {
        $permissions = [
            'config.modules.manage',

            'sales.dashboard.view',
            'sales.pos.use',
            'sales.orders.manage',
            'sales.bills.manage',
            'sales.qr.verify',
            'sales.payments.manage',
            'sales.debts.manage',
            'sales.clients.manage',

            'ops.stores.manage',
            'ops.stores.view',
            'ops.dining_tables.manage',
            'ops.dining_tables.view',
            'ops.menu.categories.manage',
            'ops.menu.products.manage',

            'hr.employees.view',
            'hr.employees.manage',
            'hr.positions.manage',
            'hr.payroll.view',
            'hr.payroll.manage',

            'inventory.stock.view',
            'inventory.stock.adjust',

            'admin.users.manage',
            'admin.settings.manage',
            'admin.audit.view',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $sync = fn (string $roleName, array $names) => Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web'])->syncPermissions($names);

        $sync('super_admin', $permissions);
        $sync('owner', $permissions);
        $sync('general_manager', array_values(array_diff($permissions, ['config.modules.manage'])));
        $sync('cashier', [
            'sales.dashboard.view', 'sales.pos.use', 'sales.orders.manage', 'sales.bills.manage',
            'sales.payments.manage', 'sales.debts.manage', 'sales.clients.manage', 'sales.qr.verify',
        ]);
        $sync('wait_staff', [
            'sales.dashboard.view', 'sales.orders.manage', 'sales.clients.manage',
            'ops.dining_tables.view', 'ops.stores.view',
        ]);
        $sync('stock_keeper', [
            'sales.dashboard.view', 'inventory.stock.view', 'inventory.stock.adjust',
            'ops.stores.view', 'ops.stores.manage', 'ops.menu.products.manage',
        ]);
        $sync('hr_admin', [
            'sales.dashboard.view', 'hr.employees.view', 'hr.employees.manage', 'hr.positions.manage',
            'hr.payroll.view', 'hr.payroll.manage',
        ]);
        $sync('accountant', [
            'sales.dashboard.view', 'sales.bills.manage', 'sales.payments.manage', 'sales.debts.manage',
            'sales.clients.manage', 'sales.orders.manage',
        ]);
        $sync('manager', array_values(array_diff($permissions, ['config.modules.manage', 'admin.users.manage'])));

        $moduleRows = [
            ['key' => 'module_pos', 'group' => 'sales', 'value' => '1', 'active' => true],
            ['key' => 'module_qr_verify', 'group' => 'sales', 'value' => '1', 'active' => true],
            ['key' => 'module_debts', 'group' => 'sales', 'value' => '1', 'active' => true],
            ['key' => 'module_reports', 'group' => 'sales', 'value' => '1', 'active' => true],
            ['key' => 'module_user_management', 'group' => 'administration', 'value' => '1', 'active' => true],
            ['key' => 'module_hr_payroll', 'group' => 'hr_payroll', 'value' => '1', 'active' => true],
            ['key' => 'module_hr_employees', 'group' => 'hr_payroll', 'value' => '1', 'active' => true],
            ['key' => 'module_inventory', 'group' => 'inventory', 'value' => '1', 'active' => true],
        ];
        foreach ($moduleRows as $m) {
            Setting::query()->updateOrCreate(
                ['key' => $m['key']],
                ['value' => $m['value'], 'is_active' => $m['active'], 'setting_group' => $m['group']]
            );
        }

        Position::firstOrCreate(['title' => 'General Staff']);
        Position::firstOrCreate(['title' => 'Waiter']);
        Position::firstOrCreate(['title' => 'Cashier']);
        Position::firstOrCreate(['title' => 'Cook']);
        Position::firstOrCreate(['title' => 'Manager']);
    }
}
