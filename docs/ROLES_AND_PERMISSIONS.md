# Roles, permissions, and users

## Naming

- **Database columns** use `snake_case` (Laravel default).
- **Permission names** use dot groups: `sales.pos.use`, `hr.employees.manage`, `inventory.stock.view`, etc.
- **PHP** methods/properties follow normal PSR style (`camelCase` methods, `PascalCase` classes).

## Permissions (summary)

| Group | Permissions |
|-------|-------------|
| **config** | `config.modules.manage` |
| **sales** | `sales.dashboard.view`, `sales.pos.use`, `sales.orders.manage`, `sales.bills.manage`, `sales.qr.verify`, `sales.payments.manage`, `sales.debts.manage`, `sales.clients.manage` |
| **ops** | `ops.stores.manage`, `ops.stores.view`, `ops.dining_tables.manage`, `ops.dining_tables.view`, `ops.menu.categories.manage`, `ops.menu.products.manage` |
| **hr** | `hr.employees.view`, `hr.employees.manage`, `hr.positions.manage`, `hr.payroll.view`, `hr.payroll.manage` |
| **inventory** | `inventory.stock.view`, `inventory.stock.adjust` |
| **admin** | `admin.users.manage`, `admin.settings.manage` |

## Roles

| Role | Typical use |
|------|-------------|
| `super_admin` / `owner` | Full access |
| `general_manager` | Almost all except global module kill-switch |
| `manager` | Operations + sales; no user admin |
| `cashier` | POS, bills, payments, clients |
| `wait_staff` | Orders, clients, floor (tables) read |
| `stock_keeper` | Stock + stores + products |
| `hr_admin` | Employees, positions, payroll |
| `accountant` | Bills, payments, debts, orders |

## Employees vs application users

- **Employee** = HR record (waiter, cook, etc.). Use **Position** `Waiter` instead of a separate Waiter entity.
- **User** = login. Not every employee gets one.
- To grant login: **Users → Add user** → optionally **link employee** (only employees without an account). That sets `employees.user_id` and `can_access_app = true`.

## Feature toggles (Settings)

Grouped in **Feature config**: `sales`, `hr_payroll`, `inventory`, `administration`. Turning a module off hides its sidebar block.
