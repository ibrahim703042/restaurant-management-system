POS (Point of Sale) Module
1. Purpose of POS
The POS interface allows the cashier to:
Quickly select food or drinks


Create an order


Generate a bill


Receive payment


Assign debt if the client cannot pay immediately


It is designed for fast service in restaurants and bars.
2. POS Main Functions
1. Select Menu Items
The POS screen shows all foods and drinks.
Example categories:
Beer


Wine


Soft drinks


Cocktails


Food


Snacks


Cashier clicks items to add them to the order.
Information displayed:
Food name
Price
Quantity
Total price


2. Create Order
The POS automatically creates an order when items are added.
Order includes:
Order number
Items selected
Quantity
Total price
Date


3. Choose or Add Client
Before confirming the order, the cashier can:
Search for an existing client
Select the client
Or add a new client






Client information:
Name
Phone number


This is important for debt tracking.
4. Generate Bill with QR Code
After confirming the order:
The system will:
Generate the bill
Create a QR Code
Save the bill in the database
Allow printing


The QR code can later be scanned to verify the bill.
5. Payment Processing
The POS allows different payment methods.
Payment methods:
Cash
Mobile Money
Bank payment


The cashier selects the method and confirms payment.



6. Debt Option
If the client cannot pay immediately, the cashier can mark the bill as debt.
Example:
Bill = $40
 Client pays = $10
 Remaining debt = $30
The system records:
Amount paid
Remaining balance


7. Print Receipt
After payment or debt confirmation, the POS can:
Print receipt
Display QR code
Save transaction











3. POS Interface Layout (Example)
Typical POS screen layout:
Left side:
Food categories
Menu items


Right side:
Order list
Quantity
Total price


Bottom section:
Select client
Payment method
Pay button
Debt button
Print receipt






4. POS Workflow

Customer orders drinks/food
 		↓
 Cashier selects items in POS
 		↓
 System calculates total
 		↓
 Cashier selects client or adds new one
		 ↓
 System generates bill with QR code
	 	↓
Customer:
Pays (Cash / Mobile money / Bank)
OR
Bill becomes debt
5. Updated System Modules
Your system now includes:
Authentication (Login)
Dashboard
POS (Point of Sale)
Order Management
Billing
QR Code Verification
Payment Management
Debt Management
Client Management
Menu Management
Food Management
Category Management
6. POS Hardware (Optional but common)
In a real restaurant POS system may use:
Barcode / QR scanner
Receipt printer
Touch screen
Cash drawer
But your project can work with only a computer or tablet.

---

## 7. Implementation (this codebase)

- **Route**: `/pos` — pick category products, build cart, choose client (required for debt/partial pay), payment method, amount paid, **Complete sale**.
- Creates **order** + **order_items** + **bill** with **QR token**; redirects to bill detail (QR + verify link).
- **Cash / mobile money / bank**: set amount paid = total for full payment; **Debt** or amount &lt; total records **debt** and updates client **debt_balance** when client is selected.
- **Print**: from bill detail or bills list → print-friendly receipt with QR.

