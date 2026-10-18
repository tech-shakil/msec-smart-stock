# MOHAMED SATHAK ENGINEERING COLLEGE (MSEC)
### (An Autonomous Institution — Kilakarai, Ramanathapuram Dist., Tamil Nadu - 623 806)
**Approved by AICTE, COA New Delhi, DGS Mumbai • Affiliated to Anna University • Sponsored by Mohamed Sathak Trust**  
**Accredited by NAAC with 'A+' Grade • NBA Accredited (CSE & IT) • TNEA Counselling Code: 5907**

---

# 📑 MASTER INSTITUTIONAL REQUIREMENT & DATA COLLECTION DOSSIER
### Project Title: **MSEC SMART STOCK** (Smart Inventory, Asset & Distribution Management Portal)
**Document Version:** `2.0-DEFINITIVE (All-In-One Master Edition)`  
**Document Tracking ID:** `MSEC/AUTO/SMART-STOCK/REQ-2026/FINAL`  
**Target Authorities:** Principal, Vice Principal, Central Store Keeper, Purchase Officer, Accounts Officer, HODs, Lab Incharges, IT System Administrator  
**Objective:** Single-window comprehensive requirement collection covering all operational, technical, financial, and email data so no repeat inquiries are required.

---

## 📌 Instructions for College Administrative Staff
> [!IMPORTANT]
> This master document serves as the **single consolidated information source** to configure, seed, and operationalize the **MSEC SMART STOCK** management system.
> 
> **How to complete this dossier:**
> 1. Fill in the institutional policies, approval thresholds, and server details directly in Sections 1 through 10.
> 2. For master data (Staff, Items, IT Assets, Vendors), populate the exact column structures defined in **Section 11 (Excel Data Templates)** and attach the spreadsheets.
> 3. Provide the official **Institutional Email (SMTP)** credentials in **Section 3** for system automated alerts.
> 4. Obtain official signatures in **Section 12** to endorse deployment.

---

## 1. 🏛️ Institutional Hierarchy & Department Masters

Please verify and complete the master registry of all academic departments, centers, and stores:

### 1.1 Academic & Administrative Departments
| Dept Code | Full Department Name | Current HOD / Incharge | Official Email ID (@msec.org.in) | Mobile / Intercom |
|:---:|:---|:---|:---|:---:|
| **CSE** | Computer Science & Engineering | | | |
| **IT** | Information Technology | | | |
| **AI&DS** | Artificial Intelligence & Data Science | | | |
| **ECE** | Electronics & Communication Engineering | | | |
| **EEE** | Electrical & Electronics Engineering | | | |
| **MECH** | Mechanical Engineering | | | |
| **CIVIL** | Civil Engineering | | | |
| **CHEM** | Chemical Engineering | | | |
| **MARINE**| Marine Engineering | | | |
| **S&H** | Science & Humanities (Physics, Chemistry, Maths, English) | | | |
| **MBA** | Master of Business Administration | | | |
| **MCA** | Master of Computer Applications | | | |
| **ADMIN** | Central College Administration & Principal's Office | | | |
| **LIB** | Central Dr. Zakir Husain Library | | | |
| **EXAM** | Office of the Controller of Examinations (COE) | | | |
| **PHYS** | Physical Education & Sports Department | | | |
| **TRANS** | Campus Transport & Vehicle Maintenance Division | | | |

*List any additional research centers, incubation cells, or autonomous bodies requiring stock tracking:*  
`__________________________________________________________________________________________________`

---

### 1.2 Laboratory & Workshop Registry
For each department, please specify all distinct laboratories where equipment and assets reside:
| Department | Laboratory / Workshop Name | Room / Lab No. | Incharge Faculty / Staff |
|:---|:---|:---|:---|
| CSE | Linux Laboratory | Main Block - Lab 201 | Ms. M.K. Merlin Rithika (merlinrithika21@gmail.com) |
| CSE | CASE Tools Laboratory | Main Block - Lab 304 | Ms. M.K. Merlin Rithika (merlinrithika21@gmail.com) |
| CSE | Android Laboratory | Main Block - Lab 205 | Ms. C. Mangala Jothi (Jothichandran1994@gmail.com) |
| IT | Network Security & Mobile App Lab | IT-201 | |
| ECE | VLSI Design & DSP Laboratory | EC-202 | |
| ECE | Optical Communication & Microwave Lab | EC-205 | |
| EEE | Power Electronics & Drives Lab | EE-101 | |
| MECH | CAD / CAM Automation Center | Workshop Bay 1 | |
| MECH | Thermal Engineering & IC Engines Lab | Workshop Bay 3 | |
| MECH | Central Lathe & Machine Shop | Machine Block | |
| CIVIL | Strength of Materials & Concrete Lab | Civil Yard | |
| MARINE | Ship Simulation & Engine Room Lab | Marine Annex | |
| CENTRAL | Central Computing Facility (CCF) | Admin Block, 2nd Fl | |

*(Attach complete master list if the college has more laboratories)*

---

### 1.3 Physical Store Locations
| Store ID / Code | Physical Store Name | Location on Campus | Key Holder / Custodian |
|:---:|:---|:---|:---|
| **STR-CENTRAL** | Central Stores (Main Receiving Warehouse) | Ground Floor, Admin Block | Central Store Keeper |
| **STR-CSE** | CSE Department Sub-Store | Dept Building, 1st Floor | CSE Stock Coordinator |
| **STR-ECE** | ECE Department Sub-Store | Dept Building, 2nd Floor | ECE Stock Coordinator |
| **STR-MECH** | Mechanical Tool Crib & Consumables Store | Workshop Complex | Workshop Technician |
| **STR-SCRAP** | Salvage & Condemnation Holding Yard | Campus Rear Compound | Condemnation Committee |

---

## 2. 👥 User Directory & Role-Based Access Control (RBAC)

The portal enforces a 15-role security model. Please nominate the responsible officer for each role:

| Role Identifier | Role Level | Functional Privileges | Nominated Officer & Official Email |
|:---|:---|:---|:---|
| **`director`** | Management | **Ultimate Financial Sanctioning Authority**, Trust Governance, Full KPIs | |
| **`principal`** | Institution | Institutional Academic Review & Recommendation, Policy Approvals, College Audit | |
| **`vice_principal`**| Admin | Administrative Review & Concurrence, Inter-dept allocations | |
| **`admin`** | System | User creation, Master data, System settings, Password resets | |
| **`store_keeper`** | Operations | Stock entry, Issue vouchers, Delivery challans, Audit | |
| **`purchase_officer`**| Procurement | Purchase Indents, PO generation, Vendor records, GRN | |
| **`accounts_officer`**| Finance | Budget verifications, PO clearance, Asset valuation | |
| **`hod`** | Department | Department Indent Endorsement (No autonomous financial sanction), Dept requisitions & balances | |
| **`dept_coordinator`**| Department | Technical spec scrutiny, Delivery challan acknowledgment, Dept transfer requests | |
| **`lab_incharge`** | Laboratory | Lab consumable indents, Asset damage logs, Legacy equipment onboarding (approx cost, no GST bills required) | |
| **`technician`** | User Level | Consumable requests, Tool crib entries, Repair reporting | |
| **`auditor`** | Oversight | Read-only inspection of stock ledger, Verification, Logs | |
| **`management_viewer`**| Oversight | Read-only executive dashboards and statutory reports | |

---

## 3. 📧 Official College Email & SMTP Server Setup (CRITICAL)

To enable automated email alerts (indent notifications, multi-tier approvals, delivery alerts, and low-stock warnings), the system requires institutional mail configuration.

### 3.1 Official Email Domain
* Official Staff Email Domain: **`@msec.org.in`** / **`@msec.edu.in`** *(Please confirm exact domain)*  
* **College Mail Service Provider:**
  - [ ] Google Workspace for Education (Gmail SMTP: `smtp.gmail.com`)
  - [ ] Microsoft 365 (Outlook SMTP: `smtp.office365.com`)
  - [ ] On-Premise Institutional Mail Server (Zimbra / Postfix / Exchange)

### 3.2 System Service Account (For Outgoing Automated Notifications)
The system requires a dedicated no-reply or notification email address to send automated alerts:
* **System Sender Email ID:** `smartstock@msec.org.in` *(or `stock-alerts@msec.org.in`)*
* **Display Name:** `MSEC SMART STOCK Portal`
* **SMTP Hostname:** `________________________________________` *(e.g. smtp.gmail.com)*
* **SMTP Port:** `____________________` *(587 for TLS or 465 for SSL)*
* **Encryption Type:** `[ ] TLS  /  [ ] SSL`
* **Service Account Username:** `________________________________________`
* **Service Account Password / App Password:** `________________________________________` *(16-character Google App Password if Workspace)*

### 3.3 Automated Email Notification Trigger Matrix
Please confirm which automated emails the college wishes to activate:
| Event Trigger | Email Recipient | Notification Purpose | Enable? |
|:---|:---|:---|:---:|
| **New Indent Raised** | Department HOD | Alerts HOD that a staff member has submitted a stock request | `[x] Yes` |
| **Tier Approval Needed**| Vice Principal / Principal / Director | Alerts administrative authority that high-value sanction is awaiting | `[x] Yes` |
| **Indent Approved** | Requester & Store Keeper | Informs staff their request is approved; informs Store to prepare stock | `[x] Yes` |
| **Voucher Dispatched** | Dept Stock Coordinator | Informs department receiver that items are in transit from Central Store | `[x] Yes` |
| **Delivery Acknowledged**| Central Store Keeper | Confirms receipt of items into department sub-inventory | `[x] Yes` |
| **Low-Stock Alert** | Store Keeper & Purchase Officer | Warning triggered when stock drops below minimum reserve buffer | `[x] Yes` |
| **Equipment Fault / Damage**| Lab Incharge & Maintenance Incharge| Incident notification when computer/lab equipment is logged as damaged | `[x] Yes` |
| **Stock Discrepancy** | Principal & Auditor | Triggered when physical audit count differs from book balance | `[x] Yes` |

---

## 4. 💰 Financial Approval & Budget Hierarchy

### 4.1 Institutional Approval Ladder (Section 11)
At MSEC, departmental heads endorse indents without independent financial sanction authority. All procurement is routed through institutional concurrence up to **Management (Directorate)** for final financial sanction:

| Stage Level | Authority Role | Scope & Institutional Responsibility |
|:---:|:---|:---|
| **Level 1** | Department Coordinator | Need and technical specification verification |
| **Level 2** | Head of Department (HOD) | Department indent scrutiny and endorsement |
| **Level 3** | Central Store Keeper | Operational warehouse buffer check & official Approval Sheet |
| **Level 4** | Vice Principal | Administrative review and concurrence |
| **Level 5** | Principal | Institutional academic review and recommendation |
| **Level 6** | Director / Management | **Ultimate Financial Sanction and Fund Allocation** |
| **Level 7** | Central Store Keeper | Material fulfillment, PO generation, and dispatch |

### 4.2 Department Budget Account Heads
Does each academic department have an allocated annual inventory budget?
- [ ] Yes (Please provide account codes, e.g., `MSEC-BUD-2026-CSE`)
- [ ] No (Expenses are centralized under college administrative accounts)
* **Budget Heads / Account Codes:** `__________________________________________________________`

### 4.3 Emergency Fast-Track Protocol
* For emergency items (e.g. urgent examination paper, emergency plumbing/electrical repairs), can the Principal or Store Keeper bypass standard multi-day queuing?  
* **College Confirmation:** `__________________________________________________________________`

---

## 5. 📦 Stock Classification & Inventory Catalog

### 5.1 Consumable vs Non-Consumable Policy (Section 7)
* **Consumables (Direct expense write-off):** Items consumed upon use (A4 papers, printer toners, chemical reagents, soldering wire, drill bits, markers).
* **Non-Consumables / Capital Assets (Capitalized & Depreciated):** Equipment retaining physical identity with a lifespan > 1 year (Computers, Projectors, Oscilloscopes, Furniture, Machinery).

### 5.2 Standard Unit of Measurement (UOM) Codes
Please verify standard packaging units used in college stores:
`Nos`, `Box`, `Pack`, `Ream` (Paper), `Meter`, `Roll`, `Kilogram (Kg)`, `Litre (L)`, `Set`, `Dozen`, `Pair`.  
*Any additional specialized unit codes:* `______________________________________________________`

### 5.3 Reorder Alert Levels (Safety Buffer Stock)
Please specify minimum stock thresholds for essential high-volume consumables:
| Item Description | Typical Reorder Point | Normal Batch Order Qty |
|:---|:---:|:---:|
| A4 Copier Paper (75 / 80 GSM) | e.g., 25 Reams | 100 Reams |
| HP Laser Toner Cartridges (88A / 12A) | e.g., 5 Cartridges | 20 Cartridges |
| Whiteboard Markers (Black / Blue / Red) | e.g., 20 Nos | 100 Nos |
| Cat6 RJ45 Network Patch Cables | e.g., 30 Nos | 100 Nos |
| Electrical CFL / LED Tube Lights (4ft) | e.g., 15 Nos | 50 Nos |

---

## 6. 🚚 Vouchers, Delivery Challans & Gate Passes

### 6.1 Document Numbering Sequences
Please confirm the preferred automatic serial format for official documents:
* **Requisition / Indent:** `[ ] MSEC/REQ/2026/00001`  or  `[ ] CSE-REQ-2026-00125`
* **Store Issue Voucher:** `[ ] MSEC/VCH/2026/00001`  or  `[ ] VCH-2026-00089`
* **Inter-Department Transfer:** `[ ] MSEC/MOV/2026/00001`  or  `[ ] MOV-2026-00042`
* **Goods Receipt Note (GRN):** `[ ] MSEC/GRN/2026/00001`  or  `[ ] GRN-2026-00031`
* **Purchase Order (PO):** `[ ] MSEC/PO/2026/00001`  or  `[ ] PO-2026-00015`

### 6.2 Official Signatures Required on Printed Delivery Vouchers
Our system generates official printable vouchers featuring the Mohamed Sathak Engineering College banner. Please confirm required sign-off boxes:
- [x] **Store Keeper** (Material Dispatched By)
- [x] **Department Receiver** (Material Received & Verified By)
- [x] **Administrative Authority** (Vice Principal / Principal Sanction)
- [ ] **Campus Main Gate Security** (For outward material movement)

### 6.3 Campus Outward Gate Pass Rules
When college equipment is sent outside the campus for servicing, calibration, or warranty repair:
- Does the college require **Returnable Gate Pass (RGP)** tracking with an expected return date?  
  `[ ] Yes  /  [ ] No`
- For condemned scrap / sold equipment, is a **Non-Returnable Gate Pass (NRGP)** required?  
  `[ ] Yes  /  [ ] No`

---

## 7. 💻 IT Asset Register & Hardware Lifecycle

### 7.1 Asset Tagging & Barcode Standard
How are IT systems and laboratory equipment currently tagged?
- [ ] **System-Generated Barcodes (Code 128 / QR Code)** printed via barcode label printer
- [ ] **Physical Stencil / Paint Marking** (e.g., `MSEC / CSE / AI-LAB / SYS-042`)
- [ ] **Metallic / Aluminum Foil Asset Tags** with serial numbering

### 7.2 Hardware Specification Fields Needed
Please confirm if any additional technical parameters must be recorded for each computer/asset:
- [x] Asset Tag / Barcode ID
- [x] Asset Name, Brand, Model No.
- [x] Hardware Serial Number
- [x] MAC Address (Ethernet & Wi-Fi)
- [x] Local Static / DHCP IP Address
- [x] Processor (CPU), RAM, Storage (HDD/SSD), Graphics Card
- [x] Operating System (Windows / Ubuntu Linux) & Licensed Software
- [x] Purchase Date, Purchase Order No., Invoice No., Unit Cost (₹)
- [x] Vendor Name, Warranty End Date, AMC Provider Details
- [x] Physical Department, Lab Room No., Assigned Custodian Staff
- [ ] *Other (Specify):* `__________________________________________________________________`

### 7.3 Dead Stock & Condemnation Policy (Section 21)
Please confirm the college norms for declaring equipment obsolete or condemned:
- **Criteria for Condemnation:** Beyond economical repair (BER), obsolete technology, or physical breakage.
- **Minimum Inactivity Period:** `[ ] 2 Years  /  [ ] 3 Years  /  [ ] 5 Years`
- **Condemnation Committee Members:** Principal, HOD, Technical Expert, Accounts Officer, Store Keeper.

---

## 8. 🛒 Registered Vendor & Supplier Directory

Please list or attach the register of approved institutional vendors:
| Vendor / Supplier Name | City / Address | Contact Person & Phone | Official Email ID | GSTIN Number | Primary Goods Supplied |
|:---|:---|:---|:---|:---|:---|
| e.g., Dell Technologies Authorized Partner | Madurai / Chennai | | | | Desktop Systems, Servers |
| e.g., National Scientific Supplies | Trichy | | | | Lab Instruments, Glassware |
| e.g., Micro Tech Components | Ramanathapuram | | | | Electronic Components, ICs |
| e.g., Royal Paper & Stationery Mart | Kilakarai / Ramnad | | | | A4 Paper, Toners, Registers |

*(Attach complete vendor contact list or Excel sheet)*

---

## 9. 📊 Statutory Accreditation & Audit Compliance (NAAC & NBA)

The system automates the reports needed for external committee reviews:

### 9.1 NAAC (Criteria 4 — Infrastructure & Learning Resources)
- [x] Annual expenditure on infrastructure augmentation (excluding salary)
- [x] Annual expenditure on maintenance of academic and physical facilities
- [x] Verified **Computer-to-Student Ratio** calculation (Total working PCs &divide; Total enrolled students)
- [x] Campus Wi-Fi, Internet bandwidth, and firewall router asset log

### 9.2 NBA (National Board of Accreditation — CSE & IT Criteria)
- [x] Major laboratory equipment register showing date of procurement, total cost, and working condition
- [x] Preventive & breakdown maintenance log sheets for laboratory instruments
- [x] Laboratory safety equipment inventory (Fire extinguishers, First-aid kits, UPS battery banks)

### 9.3 Annual Physical Stock Verification (Section 23)
- Annual stock audit frequency: `[ ] Once per academic year (May / June)  /  [ ] Semester-wise`
- Are verification teams formed using cross-department faculty (e.g. ECE faculty auditing CSE store)? `[ ] Yes  /  [ ] No`

---

## 10. 🌐 Server Hosting & Campus Network Setup

Please indicate the deployment infrastructure preference:

| Infrastructure Aspect | Preferred Option (Tick One) | College Remarks |
|:---|:---|:---|
| **Server Hosting** | `[ ] College Local Server (Campus LAN)`<br>`[ ] College Data Center Dedicated VM`<br>`[ ] Cloud Server (AWS / DigitalOcean / GCP)` | Static Local IP: `____________________` |
| **Database Engine** | `[ ] SQLite (Zero configuration embedded)`<br>`[ ] MariaDB / MySQL 8.0 (Central Enterprise DB)` | Port: `3306` |
| **Backup Frequency** | `[ ] Daily automated SQL dump to external drive`<br>`[ ] Weekly institutional backup` | Backup Path: `________________________` |
| **Peripheral Hardware**| `[ ] USB Handheld Laser Barcode Scanners`<br>`[ ] A4 Laser Printers in Central Store & HOD Offices` | |

---

## 11. 📊 Excel Data Import Templates (Exact Columns to Populate)

To upload all college data in **one single step**, please have the administrative clerks or coordinators populate the following 5 standard Excel sheets matching these exact column headers:

### Template 1: Staff & Users (`Users_Master.xlsx`)
| Col A | Col B | Col C | Col D | Col E | Col F | Col G | Col H |
|:---|:---|:---|:---|:---|:---|:---|:---|
| **Staff_ID** | **Username** | **Full_Name** | **Official_Email** | **Mobile_Number** | **Department_Code** | **System_Role** | **Designation** |
| MSEC0101 | storekeeper | K. Ramasamy | store@msec.org.in | 9876543215 | ADMIN | store_keeper | Central Store Keeper |
| MSEC0205 | hod_cse | Mr. N. Balasubramanian | csehod@msec.org.in | 9443112345 | CSE | hod | Associate Professor & Head of Department (CSE) |
| MSEC0214 | coord_cse | Mr. K. Seeni Pulavar Pitchai | seenipulavar2025@gmail.com| 9842167890 | CSE | dept_coordinator | Assistant Professor & Department Stock Coordinator |
| MSEC0222 | merlin_rithika | Ms. M.K. Merlin Rithika | merlinrithika21@gmail.com | 9789012345 | CSE | lab_incharge | Assistant Professor & Lab In-charge (Linux & CASE Tools) |
| MSEC0223 | mangala_jothi | Ms. C. Mangala Jothi | Jothichandran1994@gmail.com| 9894054321 | CSE | lab_incharge | Assistant Professor & Lab In-charge (Android) |

---

### Template 2: Master Inventory Catalog (`Inventory_Items_Master.xlsx`)
| Col A | Col B | Col C | Col D | Col E | Col F | Col G | Col H |
|:---|:---|:---|:---|:---|:---|:---|:---|
| **Item_Code** | **Item_Name** | **Category** | **Classification** | **Unit_Code** | **Min_Stock_Alert** | **Standard_Cost** | **Default_Store** |
| STN-PPR-A4 | Copier Paper A4 75 GSM | Stationery | Consumable | Ream | 25 | 280.00 | STR-CENTRAL |
| IT-TNR-88A | HP 88A Laser Toner Cartridge | IT Supplies | Consumable | Nos | 5 | 850.00 | STR-CENTRAL |
| ELC-RES-10K | Resistors 10k Ohm 1/4W | Electronics | Consumable | Pack | 50 | 45.00 | STR-ECE |
| IT-SYS-DESK | Core i7 Desktop PC System | IT Hardware | Non-Consumable | Nos | 2 | 52000.00 | STR-CENTRAL |
| MEC-TOOL-MIC | Digital Vernier Caliper 150mm | Instruments | Non-Consumable | Nos | 3 | 2400.00 | STR-MECH |

---

### Template 3: IT Assets & Equipment Register (`IT_Assets_Master.xlsx`)
| Col A | Col B | Col C | Col D | Col E | Col F | Col G | Col H | Col I | Col J |
|:---|:---|:---|:---|:---|:---|:---|:---|:---|:---|
| **Asset_Tag** | **Asset_Name_Model** | **Serial_No** | **MAC_Address** | **IP_Address** | **Dept_Code** | **Lab_Name** | **Purchase_Date** | **Cost_INR** | **Status** |
| MSEC-CSE-001 | Dell OptiPlex 7010 i7/16GB/512GB | 8XJ92K1 | 00:1A:2B:3C:4D:5E | 192.168.10.45 | CSE | AI & ML Lab | 2024-06-15 | 54500.00 | In Use |
| MSEC-ECE-014 | Tektronix 100MHz Digital Oscilloscope | TK90482 | N/A | N/A | ECE | VLSI Lab | 2023-11-20 | 48000.00 | In Use |
| MSEC-SRV-001 | HPE ProLiant DL380 Rack Server | 2M23049 | 00:1E:67:D8:9F:10 | 192.168.1.10 | ADMIN | Server Room | 2022-03-10 | 285000.00 | In Use |

---

### Template 4: Approved Vendors & Suppliers (`Vendors_Master.xlsx`)
| Col A | Col B | Col C | Col D | Col E | Col F | Col G |
|:---|:---|:---|:---|:---|:---|:---|
| **Vendor_Code** | **Company_Name** | **Contact_Person** | **Phone** | **Email** | **GSTIN** | **Payment_Terms** |
| VND-001 | Micro Systems & Peripherals | R. Anand | 9840112233 | sales@microsys.in | 33AAACM1234F1Z5 | 30 Days Credit |
| VND-002 | Scientific Lab Instruments Corp | M. Ganesan | 9840223344 | info@scilabcorp.com | 33AABCS5678K1Z2 | Immediate Cheque |

---

### Template 5: Department Budget Heads (`Department_Budgets.xlsx`)
| Col A | Col B | Col C | Col D | Col E | Col F |
|:---|:---|:---|:---|:---|:---|
| **Dept_Code** | **Budget_Head_Code** | **Financial_Year** | **Category** | **Allocated_INR** | **Sanctioning_Authority** |
| CSE | MSEC-BUD-2026-CSE-CAP | 2026-2027 | Capital (Lab Equipment) | 15,00,000 | Principal / Director |
| CSE | MSEC-BUD-2026-CSE-OPX | 2026-2027 | Operational (Consumables) | 2,50,000 | HOD / Store Keeper |
| ECE | MSEC-BUD-2026-ECE-CAP | 2026-2027 | Capital (Lab Equipment) | 12,00,000 | Principal / Director |

---

## 12. ✍️ Institutional Endorsement & Authorization

Please sign below to authorize the implementation parameters, approval matrices, email server configurations, and master data imports:

| Authority Level | Official Name | Designation | Signature | Date |
|:---|:---|:---|:---|:---|
| **Central Store Incharge** | | Central Store Keeper | | |
| **Purchase Officer** | | Procurement Officer | | |
| **Accounts / Finance Officer** | | Accounts Officer | | |
| **Academic HOD Representative** | | Senior HOD | | |
| **IT System Administrator** | | Campus Network / IT Lead | | |
| **Vice Principal** | | Administrative Authority | | |
| **Principal** | | Institutional Head | | |

---

> [!TIP]
> **Submission Checklist for College Office:**
> - [ ] Completed Questionnaire (Sections 1 through 10)
> - [ ] Official Institutional SMTP Email Credentials (Section 3)
> - [ ] Populated Excel Data Files for Staff, Items, Assets, and Vendors (Section 11)
> - [ ] Sample hardcopy of physical Delivery Challan / Store Issue Voucher
> - [ ] Signed Institutional Authorization (Section 12)
