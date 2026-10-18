# MSEC SMART STOCK — Team Work Allocation & Project Handoff Guide

**Project Title:** MSEC SMART STOCK (Smart Inventory, Asset & Distribution Management System)  
**Institution:** Mohamed Sathak Engineering College (MSEC)  
**Location:** Kilakarai - 623 806, Ramanathapuram Dist. (Autonomous Institution)  
**Architecture:** Modern Modular PHP 8.2 + MariaDB / SQLite + Vanilla CSS Design System  
**Project Directory:** `C:\Users\mohds\.gemini\antigravity-ide\scratch\msec-smart-stock`  
**Team Leader Role:** Muhammad (Team Leader / System Architect)

---

## 🚀 1. How to Open & Run the Project in your IDE

### Step 1: Open the Project in Antigravity IDE / VS Code
1. Open your IDE.
2. Click **File &rarr; Open Folder...** (or press `Ctrl + K`, `Ctrl + O`).
3. Browse to and select:
   ```
   C:\Users\mohds\.gemini\antigravity-ide\scratch\msec-smart-stock
   ```
4. Click **Select Folder**. The entire project workspace will now appear directly in your IDE File Explorer!

### Step 2: Start the Web Application
Open a terminal in your IDE (`Ctrl + ~`) and run:
```powershell
# Navigate into the project folder
cd C:\Users\mohds\.gemini\antigravity-ide\scratch\msec-smart-stock

# Start the PHP development server (using your local PHP 8.2)
& "C:\xampp\php\php.exe" -S localhost:8000 -t public
```

### Step 3: Access in Browser
- Open your browser to: **`http://localhost:8000`**
- **Default Credentials:**
  - Username: `storekeeper` (or `director`, `principal`, `viceprincipal`, `hod_cse`, `coord_cse`, `lab_ai`, `superadmin`)
  - Password: `password123`
- **Instant Demo Switcher:** At the top right of every page, there is a **"🎭 Demo Role"** dropdown that lets you switch between all 14 roles with a single click!

---

## 👥 2. Junior / Colleague Work Delegation Packages

you can allocate the 10 locked modules to your team members across these 5 structured work packages:

---

### 📦 Work Package 1: Inventory Master & Stock Movements
* **Assigned To:** Junior / Colleague 1
* **Locked Modules Covered:**
  - **Module 2:** Inventory Control (Stock Overview, Stock Entry, Opening Stock, Stock Adjustments)
  - **Module 6:** Stock Movement & Inter-Department Transfers (Section 13 & 14)
* **Key Files to Work On:**
  - `models/Inventory.php` & `views/inventory/index.php`
  - `models/Movement.php` & `views/movement/index.php`
* **Assigned Deliverables & Test Cases:**
  1. Test the **Stock Adjustment** flow (Old Qty &rarr; New Qty with mandatory reason and supporting document reference).
  2. Verify the **CSE &rarr; ECE 5 Keyboards Transfer scenario** from Section 13:
     - Check status flow: `Submitted` &rarr; `Approved` &rarr; `Dispatched` &rarr; `In Transit` &rarr; `Acknowledged`.
     - Confirm CSE stock decreases by 5 and ECE stock increases by 5 upon acknowledgment.
  3. Ensure consumables and non-consumables tabs filter inventory properly.

---

### 📝 Work Package 2: Requisitions & Dynamic Approval Engine
* **Assigned To:** Junior / Colleague 2
* **Locked Modules Covered:**
  - **Module 3:** Stock Request & Indent (Section 10)
  - **Module 4:** Approval Workflow Engine (Section 11 & 30)
* **Key Files to Work On:**
  - `models/Request.php` & `core/ApprovalEngine.php`
  - `views/requests/index.php` & `views/requests/view.php`
* **Assigned Deliverables & Test Cases:**
  1. Test raising new indents with auto-generated format like `CSE-REQ-2026-00125`.
  2. Validate the **institutional governance workflow**:
     - Coordinator (Need & balance check) &rarr; HOD (Indent endorsement) &rarr; Store Keeper (Operational Gate & Approval Sheet) &rarr; VP & Principal (Administrative & Academic Concurrence) &rarr; Management / Director (Final Financial Sanction) &rarr; Store Keeper (Material fulfillment).
  3. Test the interactive **Approve / Reject** buttons with review comments.

---

### 🚚 Work Package 3: Distribution Orders & Printable Official Vouchers
* **Assigned To:** Junior / Colleague 3
* **Locked Modules Covered:**
  - **Module 5:** Stock Distribution (Section 12)
* **Key Files to Work On:**
  - `models/Distribution.php`
  - `views/distribution/index.php`
  - `views/distribution/voucher.php`
* **Assigned Deliverables & Test Cases:**
  1. Test creating Stock Issue Orders from Central Store to Department Stores (CSE, ECE, MECH).
  2. Test generating and printing the **Official College Issue Voucher / Delivery Challan**:
     - Verify MSEC letterhead, item description, unit rate, barcode visual, and 3-party signature blocks (Store Keeper, Department Receiver, Principal/VP).
  3. Test **Digital Department Acknowledgement** which updates the receiving department's live inventory.

---

### 💻 Work Package 4: IT Asset Tracking & Equipment Maintenance
* **Assigned To:** Junior / Colleague 4
* **Locked Modules Covered:**
  - **Module 7:** Asset Management & IT Asset Tracking (Section 8 & 18)
  - **Section 20 & 21:** Damaged Stock Management & Dead Stock Watchlist
* **Key Files to Work On:**
  - `models/Asset.php` & `views/assets/index.php`
  - `views/verification/index.php` (Dead Stock Table)
* **Assigned Deliverables & Test Cases:**
  1. Test IT Asset registration capturing specialized hardware fields:
     - Serial Number, MAC Address, IP Address, Configuration (CPU/RAM/SSD), AMC Details, Warranty Expiry.
  2. Test logging fault/damage on an asset and moving status to `Under Repair`.
  3. Verify the **Dead Stock** watchlist identifying items inactive for 1, 2, or 3 years (e.g., PS/2 Keyboards, Cisco Core Switch).

---

### 🛒 Work Package 5: Procurement, GRN, Reports & Audit Governance
* **Assigned To:** Junior / Colleague 5
* **Locked Modules Covered:**
  - **Module 8:** Procurement & Vendor Management (Section 6.1, 16 & 17)
  - **Module 9:** Stock Verification & Physical Count Audits (Section 23)
  - **Module 10:** Reports & Analytics (Section 26)
  - **Section 32:** Permanent Audit Trail
* **Key Files to Work On:**
  - `models/Procurement.php` & `views/procurement/index.php`
  - `models/Report.php` & `views/reports/index.php`
  - `views/admin/audit.php` & `views/verification/index.php`
* **Assigned Deliverables & Test Cases:**
  1. Test the **Goods Receipt Note (GRN)** stock inward flow (Invoice No &rarr; Material Received &rarr; Quality Verification &rarr; Auto-update Central Inventory).
  2. Verify all 5 locked report groups from Section 26:
     - Stock Reports, Movement Reports, Consumption Reports, Financial Reports, Audit Reports.
  3. Verify that every user action and adjustment writes an immutable record to `activity_logs`.

---

## 🗄️ 3. Database Deployment (MySQL / MariaDB via XAMPP)

The system currently runs on an embedded, zero-configuration SQLite database (`database/msec_stock.sqlite`) which is already pre-seeded and active.

When you want to deploy to **XAMPP MySQL / MariaDB**:
1. Open XAMPP Control Panel and click **Start** next to MySQL.
2. In phpMyAdmin or MySQL CLI, create the database:
   ```sql
   CREATE DATABASE msec_stock CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. Import the enterprise schema and seed scripts:
   - Schema: `database/schema_mysql.sql`
   - Seed data: `database/seed_mysql.sql`
4. In `config/database.php`, change `'default' => 'sqlite'` to `'default' => 'mysql'`.

---

## 📊 4. Project Structure Reference

```
msec-smart-stock/
├── config/
│   ├── app.php                   # System constants & 14 role configurations
│   └── database.php              # SQLite / MySQL PDO connection settings
├── core/
│   ├── Database.php              # Singleton PDO wrapper with auto-seeding
│   ├── Auth.php                  # RBAC authentication & demo switcher
│   └── ApprovalEngine.php        # Dynamic multi-tier approval matrix engine
├── models/
│   ├── Inventory.php             # Stock tracking, low stock & adjustments
│   ├── Request.php               # Indent requests & approval timeline
│   ├── Distribution.php          # Issue vouchers & department receipts
│   ├── Movement.php              # Inter-department transfer workflows
│   ├── Asset.php                 # IT asset specs (MAC/IP/AMC/Status)
│   ├── Procurement.php           # Vendors, POs & GRN material inward
│   └── Report.php                # Executive, department & store KPIs
├── views/
│   ├── layout/                   # Header, Sidebar (12 menus), Footer
│   ├── auth/                     # Login portal with one-click demo personas
│   ├── dashboard/                # Executive, Department & Store dashboards
│   ├── inventory/                # Master inventory & stock adjustment modal
│   ├── requests/                 # Indent log, request creation, approval queue
│   ├── distribution/             # Issue register & printable official vouchers
│   ├── movement/                 # Inter-department transfer (CSE -> ECE flow)
│   ├── assets/                   # IT asset register with hardware specs
│   ├── procurement/              # Vendor directory & GRN receiving
│   ├── verification/             # Physical count audits & dead stock watch
│   ├── reports/                  # The 5 locked report groups
│   └── admin/                    # Master data, users & permanent audit trail
├── public/
│   ├── css/style.css             # Modern design system & print styles
│   ├── js/app.js                 # Dynamic searches, modals & interactions
│   └── index.php                 # Front controller & action router
└── database/
    ├── msec_stock.sqlite         # Ready-to-use seeded database
    ├── schema_mysql.sql          # Enterprise MariaDB/MySQL DDL
    ├── seed_mysql.sql            # Master institutional seed script
    └── DatabaseSeeder.php        # Unified database migrator & seeder
```
