Restaurant & Bar Management System
1. Project Overview
The Restaurant & Bar Management System is an admin-side application designed to manage restaurant operations such as orders, billing, clients, payments, and financial reports.
The system helps administrators track sales, customer debts, and payment methods, while also verifying bills using QR code scanning.
2. System Scope
Included Features
1. Dashboard
The dashboard provides a summary of the restaurant’s financial and operational activities.
Reports include:
Daily income
Weekly income
Monthly income
Total orders
Total clients
Total unpaid debts
Recent transactions


Purpose:
 To help the admin monitor the business performance easily.
3. Order Management
This module manages customer orders.
Functions:
Create order
Add food items to order
Update order
Cancel order
View order history


Order information includes:
Order ID
Client
Ordered items
Quantity
Total amount
Order date
Order status

4. Billing System
After an order is completed, the system generates a bill with a QR Code.
Functions:
Generate invoice
Calculate total
Print bill
Store billing records
Attach QR code for verification


Billing information:
Bill ID
Order reference
Client
Payment status
Payment method
Date

5. QR Code Scan for Billing Verification
Each bill will contain a QR code.
When the QR code is scanned:
System will:
Display bill information
Allow admin to select an existing client
Allow admin to add a new client if not existing
Confirm payment or assign debt
Purpose:
Prevent fraud
Verify bills
Link bills to customers

6. Payment Management
The system supports multiple payment methods.
Payment methods include:
Cash
Mobile Money (E-mobile money)
Bank payment
Functions:
Select payment method
Record payment
Track payment history



Payment information:
Payment ID
Bill ID
Client
Amount
Payment method
Payment date
7. Debt Management
Some clients may not pay immediately, so the system must manage debts.
Functions:
Assign bill as debt
Track unpaid bills
View client debts
Record debt payments
Update remaining balance
Debt information:
Client
Bill ID
Amount owed
Amount paid
Remaining balance
Payment history


Example scenario:
 Client orders drinks → bill = 100.000 fbu
 Client pays 50.000 fbu → remaining debt = 50.000 fbu
8. Client Management
Manage restaurant customers.
Functions:
Add client
Edit client
Delete client
View client history
View client debts


Client information:
Client ID
Name
Phone number
Address
Debt balance
9. Menu Management
Admin manages the available menu.
Functions:
Add menu items
Edit menu
Delete menu
Activate / deactivate menu items

10. Food Management
Functions:
Add food item
Edit food item
Delete food item
Assign category
Food information:
Food name
Category
Price
Description
11. Category Management
Examples:
Beer
Wine
Soft drinks
Cocktails
Food
Functions:
Add category
Edit category
Delete category


12. make configuarable on settings also if the entrepre activate them mean the will be visable 
The system will NOT include:
UOM (Unit of Measurement)
Stock management
Inventory
Kitchen management

13. System Modules
Authentication Module (Login)
Dashboard Module
Order Management
Billing Module
QR Code Verification
Payment Management
Debt Management
Client Management
Menu Management
Food Management
Category Management


14. Database Main Tables
Key tables:
admins
clients
categories
foods
menu_items
orders
order_items
bills
payments
debts


---

## 15. Implementation notes (Laravel app)

- **Auth users** replace a separate `admins` table; roles use **Spatie Permission** (`super_admin`, `manager`, `cashier`).
- **Foods** map to **products**; categories unchanged. **Menu items** = products with `status = 1` (active) shown on POS.
- **Dashboard**: daily / weekly / monthly income from `payments`, order & client counts, total client debt, recent payments.
- **Orders / bills / payments / debts / clients**: implemented with QR verify URL on each bill (external QR image API).
- **Settings**: keys `module_pos`, `module_qr_verify`, `module_debts`, `module_reports`, `module_user_management` toggle sidebar visibility.
- **Storage**: set `FILESYSTEM_DISK=public`, run `php artisan storage:link`. Product images → `storage/app/public/products`, employees → `employees`.
- **First admin (seeder)**: email `kwizera.ibrahim@gmail.com`, password `admin` — roles `super_admin`, `manager`, `cashier` (full sidebar + permissions).

