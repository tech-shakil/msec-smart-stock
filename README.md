# MSEC SMART STOCK
### Smart Inventory, Asset & Distribution Management Portal
**Mohamed Sathak Engineering College (Autonomous), Kilakarai**  
*Approved by AICTE, Affiliated to Anna University, Accredited by NAAC with 'A+' Grade & NBA Accredited Programmes*

---

## 📌 Project Overview
**MSEC SMART STOCK** is an institutional ERP web application engineered to unify inventory control, asset lifecycle tracking, multi-tier procurement workflows, central store distribution, laboratory maintenance, and statutory accreditation reporting across all engineering departments.

The portal enforces institutional governance where **no purchase request bypasses the Central Store Keeper**, account creation is strictly governed by the **System Administrator**, and statutory data for **NAAC (Criterion 4)** and **NBA** are dynamically aggregated.

---

## 🚀 Quick Start Guide

### 1. Launch the Server
Double-click `run.bat` or run via PowerShell:
```powershell
& 'C:\xampp\php\php.exe' -S localhost:8000 -t public
```

### 2. Open the Portal
Navigate to:
```
http://localhost:8000
```
*(If unauthenticated, you will be directed to `http://localhost:8000/index.php?route=login`)*

### 3. Database Status
The zero-configuration database is automatically provisioned and pre-seeded at `database/msec_stock.sqlite`. It seamlessly supports SQLite out of the box and can switch to MySQL/MariaDB in `config/database.php`.

To re-seed fresh test data at any time:
```powershell
& 'C:\xampp\php\php.exe' database/DatabaseSeeder.php
```

---

## 🔐 Institutional Security & Authentication Rules

1. **Zero Public Self-Registration**:
   - There is strictly **no public "Sign Up" or "Create Account" option**.
   - All accounts are created and provisioned exclusively by the **System Administrator** (`index.php?route=admin_users`).
2. **Admin-Only Password Reset Protocol**:
   - In accordance with college security policies, there is **no self-service email OTP or forgot password link** on the login page.
   - Users who forget their password must contact the System Administrator in person.
   - The Administrator verifies physical identity and resets the credential via the Admin Console, which is immutably logged into the audit ledger with Admin ID, IP address, and timestamp.
3. **Bruteforce Defense**:
   - Accounts are automatically locked after **5 consecutive failed login attempts**.
   - Locked accounts can only be unlocked by the System Administrator with an audit justification memo.

---

## 👥 Baseline Demonstration Accounts (13 Institutional Roles)

All accounts share the default development password: **`password123`**

| Role | Username / Email | Full Name | Department / Station |
|---|---|---|---|
| **System Admin** | `admin` | Dr. K. Ramanathan | System Administration & IT Cell |
| **Director** | `director` | Dr. S. Syed Mohamed | Executive Directorate |
| **Principal** | `principal` | Dr. N. Mahendran | Principal's Secretariat |
| **Vice Principal** | `vice_principal` | Dr. A. Peer Mohamed | Academic Administration |
| **Store Keeper** | `store_keeper` | S. Mohamed Riaz | Central Store Operational Gateway |
| **Purchase Officer**| `purchase_officer` | M. Senthil Kumar | Procurement Cell |
| **Accounts Officer**| `accounts_officer` | R. Narayanan | Finance & Accounts |
| **HOD (CSE)** | `csehod@msec.org.in` | Mr. N. Balasubramanian | Computer Science & Engineering |
| **Dept Coordinator**| `seenipulavar2025@gmail.com` | Mr. K. Seeni Pulavar Pitchai | CSE Department Store & Indents |
| **Lab In-Charge (Linux & CASE)** | `merlinrithika21@gmail.com` | Ms. M.K. Merlin Rithika | Linux & CASE Tools Laboratories |
| **Lab In-Charge (Android)** | `Jothichandran1994@gmail.com` | Ms. C. Mangala Jothi | Android Laboratory |
| **Technician** | `tech_hardware` | M. Arumugam | Central Hardware Repair Bay |
| **Auditor** | `auditor_ext` | CA. V. Swaminathan | Statutory & Internal Audit Cell |
| **Management Viewer**| `viewer_mgmt` | H. Farook Ahmed | Governing Board Management |

*Note: The redundant `faculty` role has been decommissioned. In academic departments, lab requisitions and physical custody are raised and managed directly by designated Lab In-charges and Department Coordinators.*

*Tip: A Quick Persona Switcher is available at the top-right header for evaluators and committee members to switch roles seamlessly.*

---

## 🔄 Core Workflows & Features

### 1. Store Keeper Operational Gateway & Approval Sheets
- **The Gateway Principle**: The Store Keeper is the mandatory verification gate between department authorization and institutional sanction.
- **Approval Sheet (`views/approval_sheets/view.php`)**: The Store Keeper inspects catalog balances, physical stores, vendor quotations, and prepares the official printable Approval Sheet (`AS-2026-0001`) with institutional signature blocks.
- **Institutional Approval Hierarchy**:
  - Department Need & Technical Scrutiny (Originator & Coordinator)
  - Department Indent Endorsement (HOD)
  - Central Store Keeper Operational Gate & Approval Sheet
  - Administrative Review & Concurrence (Vice Principal)
  - Institutional Academic Review & Recommendation (Principal)
  - **Ultimate Financial Sanction & Budget Allocation (Management / Directorate)**
  - Material Fulfillment, PO Generation & Delivery (Store Keeper)

### 2. Procurement & Vendor Management (`index.php?route=procurement`)
- **Quotation Comparison Statement**: Side-by-side comparison of 3 vendor quotes with rates, taxes, delivery periods, warranty, and L1 recommendations.
- **Purchase Orders (PO)**: Generation of official POs with delivery terms, payment milestones, and printable layout (`index.php?route=view_po&id=1`).
- **Goods Receipt Notes (GRN)**: Quality inspection entry for received lots with accepted/rejected quantities, automatically updating central inventory balances.

### 3. Stock Distribution & Vouchers (`index.php?route=distribution`)
- Official Store Issue Vouchers and Delivery Challans with barcodes.
- Two-party physical and digital delivery acknowledgment (`index.php?route=view_voucher&id=1`).

### 4. Inter-Department Transfers (`index.php?route=movement`)
- Reallocation workflows (e.g. CSE transferring surplus keyboards to ECE) with multi-stage progression: `Submitted` &rarr; `Store Keeper Approved` &rarr; `Dispatched` &rarr; `Acknowledged`.

### 5. IT Asset Register & Repair Bay (`index.php?route=assets` & `route=maintenance`)
- Tracking of MAC addresses, IP addresses, serial numbers, warranty expiry, AMC details, and custodians.
- Technician workbench for diagnosing SMPS faults, logging repair costs, and requisitioning replacement spares.

### 6. Accreditation & Statutory Reports (`index.php?route=reports`)
- **NAAC Criteria 4 (IT & Infrastructure)**: PC-to-student ratio calculation (**1 : 1.8** with 1,480 student PCs), 1 Gbps leased line, Wi-Fi APs, and smart halls.
- **NBA Criteria (Lab Equipment & Maintenance)**: NABL calibration dates, next service due dates, and major equipment (> ₹50,000) utilization ledgers.
- **One-Click CSV Export**: Instant statutory download via `Export to CSV`.

### 7. Legacy & Existing Equipment Onboarding (Lab In-Charge Portal)
- **Zero-Barrier Ingestion**: Lab In-Charges can register pre-existing laboratory instruments, desktop terminals, oscilloscopes, and legacy gear already installed in labs.
- **No GST / Tax Invoices Required**: Recognizes that historical paper invoices may be lost or archived; accepts approximate/estimated valuation (`purchase_cost`) to bring physical lab hardware under formal institutional accountability.
- **Automated Legacy Tagging**: Automatically synthesizes standard college asset tags (`MSEC-{LAB}-LEGACY-{RAND}`) with department, laboratory, and custodian bindings.

---

## 📁 Repository Structure

```
msec-smart-stock/
├── config/
│   ├── app.php                # Application metadata & currency constants
│   └── database.php           # SQLite & MySQL configuration
├── core/
│   ├── ApprovalEngine.php     # 4-tier approval state machine & Approval Sheet builder
│   ├── AuditLogger.php        # Immutable audit logging engine
│   ├── Auth.php               # Granular RBAC, bruteforce lock, admin user control
│   └── Database.php           # PDO singleton instance
├── database/
│   ├── DatabaseSeeder.php     # Schema definitions & 14-role baseline dataset
│   ├── msec_stock.sqlite      # SQLite database file
│   └── schema_mysql.sql       # MySQL/MariaDB DDL reference
├── docs/
│   └── UI_DOCUMENTATION.md    # Comprehensive UI, UX and workflow documentation
├── models/
│   ├── ApprovalSheet.php      # Store Keeper verification & approval sheet model
│   ├── Asset.php              # IT equipment and hardware assets
│   ├── Budget.php             # Department budget heads and commitments
│   ├── Distribution.php       # Store issue vouchers and challans
│   ├── Inventory.php          # Items, stores, stock balances, and dead stock
│   ├── Maintenance.php        # Service tickets and technician workbench
│   ├── Movement.php           # Inter-department transfers
│   ├── Notification.php       # Real-time role notification center
│   ├── Procurement.php        # Vendors, quotations, POs, and GRNs
│   ├── Report.php             # Statutory, NAAC/NBA, and financial reports
│   └── Request.php            # Stock requisitions and approval logs
├── public/
│   ├── css/style.css          # Enterprise institutional design system
│   ├── images/                # Official MSEC letterhead and college seal
│   ├── js/app.js              # Modals, searches, CSV exports, dynamic UI
│   └── index.php              # Front controller and router
├── views/
│   ├── admin/                 # User directory, security logs, audit ledger, settings
│   ├── approval_sheets/       # Printable Store Keeper Approval Sheet (AS-XXXX)
│   ├── assets/                # IT hardware register and configuration tagging
│   ├── auth/                  # Institutional login page
│   ├── budget/                # Annual department budget allocations
│   ├── dashboards/            # 14 genuine role-specific dashboard views
│   ├── distribution/          # Stock issue register and printable voucher
│   ├── inventory/             # Central store catalogue and stock adjustments
│   ├── layout/                # Global header with notification center & sidebar
│   ├── maintenance/           # Equipment repair bay and technician workbench
│   ├── movement/              # Inter-department stock transfers
│   ├── procurement/           # POs, comparative quotations, GRNs, vendors
│   ├── reports/               # NAAC, NBA, stock, movement, and financial reports
│   ├── requests/              # Indent creation and multi-tier approval view
│   └── verification/          # Physical audit counts and dead stock watchlist
└── run.bat                    # One-click Windows launch script
```

---

## 📜 Accreditation Compliance Statement
This portal has been developed to adhere strictly to:
- **NAAC Manual for Autonomous Colleges**: Key Indicator 4.3 (IT Infrastructure & Student-Computer Ratio)
- **NBA Tier-1 Accreditation Manual**: Criterion 6 (Facilities & Technical Support, Lab Equipment Maintenance & Calibration)
- **Institutional Financial & Stores Manual**: Mohamed Sathak Trust Store Purchase & Verification Guidelines