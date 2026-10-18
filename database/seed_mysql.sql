-- ==========================================================
-- MSEC SMART STOCK - Seed Data Script
-- Based on the 37-page locked requirements specification
-- ==========================================================

-- Organizations
INSERT INTO organizations (id, code, name, address, phone, email, established_year) VALUES
(1, 'MSEC', 'Mohamed Sathak Engineering College', 'Kilakarai - 623 806, Ramanathapuram Dist., Tamil Nadu', '+91 4567 241327', 'stock@msec.org.in', 1984)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Departments (Complete Institutional Registry)
INSERT INTO departments (id, org_id, code, name) VALUES
(1, 1, 'CSE', 'Computer Science & Engineering'),
(2, 1, 'ECE', 'Electronics & Communication Engineering'),
(3, 1, 'EEE', 'Electrical & Electronics Engineering'),
(4, 1, 'MECH', 'Mechanical Engineering'),
(5, 1, 'CIVIL', 'Civil Engineering'),
(6, 1, 'ADMIN_DEPT', 'Central Administration'),
(7, 1, 'IT', 'Information Technology'),
(8, 1, 'AIDS', 'Artificial Intelligence & Data Science'),
(9, 1, 'CHEM', 'Chemical Engineering'),
(10, 1, 'MARINE', 'Marine Engineering'),
(11, 1, 'SH', 'Science & Humanities'),
(12, 1, 'MBA', 'Master of Business Administration'),
(13, 1, 'MCA', 'Master of Computer Applications'),
(14, 1, 'LIB', 'Central Dr. Zakir Husain Library'),
(15, 1, 'INCUBATION', 'MSEC Incubation & Innovation Center'),
(16, 1, 'EXAM', 'Office of the Controller of Examinations (COE)'),
(17, 1, 'PHYS', 'Physical Education & Sports Department')
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Laboratories (Section 9 & 11)
INSERT INTO laboratories (id, dept_id, code, name, location) VALUES
(1, 1, 'CSE-LINUX', 'Linux Laboratory', 'Main Block - Floor 2 - Lab 201'),
(2, 1, 'CSE-CASE', 'CASE Tools Laboratory', 'Main Block - Floor 3 - Lab 304'),
(3, 1, 'CSE-ANDROID', 'Android Laboratory', 'Main Block - Floor 2 - Lab 205'),
(5, 2, 'ECE-COMM', 'Communication Systems Lab', 'Science Block - Floor 1 - Lab 102'),
(6, 3, 'EEE-POW', 'Power Electronics Lab', 'Science Block - Floor 2 - Lab 201'),
(7, 4, 'MECH-CAD', 'CAD/CAM Lab', 'Workshop Block - Floor 1 - Lab 101')
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Roles (Section 3)
INSERT INTO roles (role_key, name, level, description) VALUES
('super_admin', 'Super Admin', 'System Level', 'Complete application control'),
('director', 'Director', 'Management', 'Strategic monitoring and high-value approvals'),
('principal', 'Principal', 'Institution Level', 'Final approval authority and institutional overview'),
('vice_principal', 'Vice Principal', 'Administration', 'Administrative approval and daily monitoring'),
('admin', 'Admin', 'Central Administration', 'User and master configuration management'),
('store_keeper', 'Store Keeper', 'Operational', 'Central stock receipt, issue, GRN and dispatch'),
('purchase_officer', 'Purchase Officer', 'Procurement', 'Purchase orders and vendor management'),
('accounts_officer', 'Accounts Officer', 'Finance', 'Financial verification and invoices'),
('hod', 'HOD', 'Department', 'Department stock approval and quota management'),
('dept_coordinator', 'Department Stock Coordinator', 'Department', 'Department inventory control'),
('lab_incharge', 'Lab Incharge', 'Laboratory', 'Lab equipment, consumable requisitions and safety'),
('technician', 'Technician', 'User', 'Lab maintenance and stock usage tracking'),
('auditor', 'Auditor', 'Read Only', 'Audit verification and variance inspection'),
('management_viewer', 'Management Viewer', 'Read Only', 'Read-only dashboard monitoring')
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Users (Standard test password: password123)
-- Hash generated for 'password123': $2y$10$wE16gX3eBup5yS90M5yv3OSYtQ2wVfB41bC17l4yJ5Xv5cW9Q0v3W
INSERT INTO users (id, username, password_hash, full_name, email, phone, role_key, dept_id, lab_id, status) VALUES
(1, 'superadmin', '$2y$10$wE16gX3eBup5yS90M5yv3OSYtQ2wVfB41bC17l4yJ5Xv5cW9Q0v3W', 'Super Administrator', 'superadmin@msec.edu.in', '+91 9876543210', 'super_admin', 6, NULL, 'Active'),
(2, 'director', '$2y$10$wE16gX3eBup5yS90M5yv3OSYtQ2wVfB41bC17l4yJ5Xv5cW9Q0v3W', 'Dr. K. S. Babai (Director)', 'director@msec.edu.in', '+91 9876543211', 'director', 6, NULL, 'Active'),
(3, 'principal', '$2y$10$wE16gX3eBup5yS90M5yv3OSYtQ2wVfB41bC17l4yJ5Xv5cW9Q0v3W', 'Dr. C. Umarani (Principal)', 'principal@msec.edu.in', '+91 9876543212', 'principal', 6, NULL, 'Active'),
(4, 'viceprincipal', '$2y$10$wE16gX3eBup5yS90M5yv3OSYtQ2wVfB41bC17l4yJ5Xv5cW9Q0v3W', 'Dr. S. Rajendran (Vice Principal)', 'vp@msec.edu.in', '+91 9876543213', 'vice_principal', 6, NULL, 'Active'),
(5, 'admin', '$2y$10$wE16gX3eBup5yS90M5yv3OSYtQ2wVfB41bC17l4yJ5Xv5cW9Q0v3W', 'System Administrator', 'admin@msec.edu.in', '+91 9876543214', 'admin', 6, NULL, 'Active'),
(6, 'storekeeper', '$2y$10$wE16gX3eBup5yS90M5yv3OSYtQ2wVfB41bC17l4yJ5Xv5cW9Q0v3W', 'K. Ramasamy (Central Store Keeper)', 'store@msec.edu.in', '+91 9876543215', 'store_keeper', 6, NULL, 'Active'),
(7, 'purchaseofficer', '$2y$10$wE16gX3eBup5yS90M5yv3OSYtQ2wVfB41bC17l4yJ5Xv5cW9Q0v3W', 'P. Natarajan (Purchase Officer)', 'purchase@msec.edu.in', '+91 9876543216', 'purchase_officer', 6, NULL, 'Active'),
(8, 'accountsofficer', '$2y$10$wE16gX3eBup5yS90M5yv3OSYtQ2wVfB41bC17l4yJ5Xv5cW9Q0v3W', 'A. Mohamed Hameed (Accounts Officer)', 'accounts@msec.org.in', '+91 9876543217', 'accounts_officer', 6, NULL, 'Active'),
(9, 'hod_cse', '$2y$10$wE16gX3eBup5yS90M5yv3OSYtQ2wVfB41bC17l4yJ5Xv5cW9Q0v3W', 'Mr. N. Balasubramanian (HOD CSE)', 'csehod@msec.org.in', '+91 94431 12345', 'hod', 1, NULL, 'Active'),
(10, 'hod_ece', '$2y$10$wE16gX3eBup5yS90M5yv3OSYtQ2wVfB41bC17l4yJ5Xv5cW9Q0v3W', 'Dr. G. Sumathi (HOD ECE)', 'hod.ece@msec.edu.in', '+91 9876543219', 'hod', 2, NULL, 'Active'),
(11, 'coord_cse', '$2y$10$wE16gX3eBup5yS90M5yv3OSYtQ2wVfB41bC17l4yJ5Xv5cW9Q0v3W', 'Mr. K. Seeni Pulavar Pitchai (CSE Coordinator)', 'seenipulavar2025@gmail.com', '+91 98421 67890', 'dept_coordinator', 1, NULL, 'Active'),
(12, 'merlin_rithika', '$2y$10$wE16gX3eBup5yS90M5yv3OSYtQ2wVfB41bC17l4yJ5Xv5cW9Q0v3W', 'Ms. M.K. Merlin Rithika (Linux & CASE Tools)', 'merlinrithika21@gmail.com', '+91 97890 12345', 'lab_incharge', 1, 1, 'Active'),
(14, 'tech_cse', '$2y$10$wE16gX3eBup5yS90M5yv3OSYtQ2wVfB41bC17l4yJ5Xv5cW9Q0v3W', 'T. Murugan (Lab Technician)', 'tech.cse@msec.edu.in', '+91 9876543223', 'technician', 1, 1, 'Active'),
(15, 'auditor', '$2y$10$wE16gX3eBup5yS90M5yv3OSYtQ2wVfB41bC17l4yJ5Xv5cW9Q0v3W', 'S. Narayanan (Internal Auditor)', 'audit@msec.edu.in', '+91 9876543224', 'auditor', 6, NULL, 'Active'),
(16, 'viewer', '$2y$10$wE16gX3eBup5yS90M5yv3OSYtQ2wVfB41bC17l4yJ5Xv5cW9Q0v3W', 'Governing Body Observer', 'viewer@msec.edu.in', '+91 9876543225', 'management_viewer', 6, NULL, 'Active'),
(17, 'mangala_jothi', '$2y$10$wE16gX3eBup5yS90M5yv3OSYtQ2wVfB41bC17l4yJ5Xv5cW9Q0v3W', 'Ms. C. Mangala Jothi (Android Lab)', 'Jothichandran1994@gmail.com', '+91 98940 54321', 'lab_incharge', 1, 3, 'Active')
ON DUPLICATE KEY UPDATE full_name=VALUES(full_name);

-- Stores (Section 9)
INSERT INTO stores (id, code, name, type, dept_id, keeper_user_id) VALUES
(1, 'STORE-CENTRAL', 'MSEC Central Store', 'CENTRAL', NULL, 6),
(2, 'STORE-CSE', 'CSE Department Store', 'DEPARTMENT', 1, 11),
(3, 'STORE-ECE', 'ECE Department Store', 'DEPARTMENT', 2, 10),
(4, 'STORE-EEE', 'EEE Department Store', 'DEPARTMENT', 3, NULL),
(5, 'STORE-MECH', 'Mechanical Department Store', 'DEPARTMENT', 4, NULL),
(6, 'STORE-CIVIL', 'Civil Department Store', 'DEPARTMENT', 5, NULL)
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Units
INSERT INTO units (id, code, name) VALUES
(1, 'Nos', 'Numbers / Units'),
(2, 'Reams', 'Paper Reams'),
(3, 'Box', 'Box / Pack'),
(4, 'Meters', 'Meters'),
(5, 'Kg', 'Kilograms'),
(6, 'Litre', 'Litres'),
(7, 'Set', 'Set')
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Item Categories (Section 7)
INSERT INTO item_categories (id, name, type, description) VALUES
(1, 'Stationery & Paper', 'CONSUMABLE', 'A4 sheets, pens, notebooks, office paper'),
(2, 'IT Consumables', 'CONSUMABLE', 'Printer cartridges, toners, ribbons'),
(3, 'Electrical & Wiring', 'CONSUMABLE', 'Electrical wires, connectors, fuses, switches'),
(4, 'Electronic Components', 'CONSUMABLE', 'Breadboards, resistors, ICs, microcontrollers'),
(5, 'Lab Chemicals & Reagents', 'CONSUMABLE', 'Acids, bases, solvents, indicators'),
(6, 'IT Equipment & Hardware', 'NON_CONSUMABLE', 'Desktop systems, laptops, servers, switches, printers, monitors'),
(7, 'Lab Instruments & Machinery', 'NON_CONSUMABLE', 'Oscilloscopes, function generators, testing rigs'),
(8, 'Furniture & Fixtures', 'NON_CONSUMABLE', 'Executive chairs, lab benches, faculty desks')
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Items (Locked Examples from Section 7, 8, 10, 13, 21, 22)
INSERT INTO items (id, item_code, item_name, category_id, subcategory_id, unit_id, is_consumable, min_stock, max_stock, unit_cost, description) VALUES
(1, 'IT-CBL-001', 'HDMI Cable 2.0 (3 Meter)', 2, NULL, 1, 1, 15.00, 100.00, 350.00, 'High speed gold plated HDMI cable for smart classrooms & labs'),
(2, 'IT-KEY-002', 'Dell USB Multimedia Keyboard', 6, NULL, 1, 0, 10.00, 200.00, 650.00, 'Standard 104-key USB wired keyboard'),
(3, 'STN-PPR-001', 'A4 Copier Sheets (75 GSM, 500 Sheets)', 1, NULL, 2, 1, 25.00, 500.00, 280.00, 'White multipurpose photocopying and printing paper'),
(4, 'IT-TNR-001', 'HP LaserJet 88A Toner Cartridge', 2, NULL, 1, 1, 5.00, 40.00, 1450.00, 'Black genuine toner cartridge for office printers'),
(5, 'ELC-WIR-001', 'Copper Wire 1.5 sq mm (90m Roll)', 3, NULL, 4, 1, 10.00, 100.00, 1250.00, 'FR PVC insulated building copper wire'),
(6, 'CHM-SLN-001', 'Hydrochloric Acid GR 37% (2.5L)', 5, NULL, 6, 1, 4.00, 20.00, 850.00, 'Analytical grade lab chemical reagent'),
(7, 'IT-SYS-001', 'Dell OptiPlex 7090 Desktop (i7, 16GB, 512GB SSD)', 6, NULL, 1, 0, 5.00, 150.00, 68000.00, 'High performance workstation for AI & programming laboratories'),
(8, 'IT-SWT-001', 'Cisco Catalyst 24-Port Gigabit Managed Switch', 6, NULL, 1, 0, 2.00, 30.00, 30000.00, 'Layer 2/3 enterprise core switch with PoE'),
(9, 'IT-PRJ-001', 'Epson EB-E01 XGA 3300 Lumens Projector', 6, NULL, 1, 0, 3.00, 50.00, 38500.00, 'Ceiling mount classroom display projector'),
(10, 'IT-KEY-OLD', 'Legacy PS/2 Keyboard (Surplus Batch)', 6, NULL, 1, 0, 5.00, 50.00, 500.00, 'Obsolete keyboard stock without movement since 2023 - Dead Stock')
ON DUPLICATE KEY UPDATE item_name=VALUES(item_name);

-- Stock Balances in Central & Department Stores
INSERT INTO stock (store_id, item_id, available_quantity, reserved_quantity, store_location, last_movement_date) VALUES
(1, 1, 65.00, 10.00, 'Central Rack B-04', '2026-09-15'),
(1, 2, 45.00, 5.00, 'Central Rack IT-01', '2026-09-18'),
(1, 3, 10.00, 0.00, 'Central Rack Paper-A1', '2026-09-10'), -- Low Stock alert: Available 10 < Min 25!
(1, 4, 18.00, 2.00, 'Central Rack IT-03', '2026-09-12'),
(1, 7, 24.00, 0.00, 'Central Secure Vault', '2026-09-01'),
(1, 8, 3.00, 0.00, 'Central Network Rack', '2022-11-20'),  -- Dead stock: No movement since 2022! (Section 21)
(1, 10, 15.00, 0.00, 'Central Scrap Shelf C', '2023-04-10'),  -- Dead stock: 15 qty, ₹7,500 value! (Section 21)
(2, 1, 12.00, 0.00, 'CSE Lab Cabinet A', '2026-09-12'),
(2, 2, 20.00, 0.00, 'CSE Tech Store Rack 1', '2026-09-15'), -- CSE has 10 extra keyboards (Section 13)
(3, 2, 2.00, 0.00, 'ECE Component Lab', '2026-08-20')         -- ECE needs 5 keyboards (Section 13)
ON DUPLICATE KEY UPDATE available_quantity=VALUES(available_quantity);

-- IT Assets (Section 8 & 18)
INSERT INTO assets (id, asset_tag, asset_name, category_id, brand, model, serial_number, mac_address, ip_address, warranty_expiry, amc_details, configuration, purchase_date, purchase_cost, dept_id, lab_id, location, custodian_id, asset_status) VALUES
(1, 'MSEC-CSE-PC-001', 'Dell OptiPlex 7090 Desktop', 6, 'Dell', 'OptiPlex 7090', 'SN-DELL-887410', '00:1A:2B:3C:4D:5E', '192.168.10.101', '2027-08-30', 'Dell ProSupport Plus till Aug 2027', 'Intel Core i7 11th Gen, 16GB DDR4, 512GB NVMe SSD, Nvidia GTX 1650', '2024-09-01', 68000.00, 1, 2, 'AI Lab - Terminal 01', 12, 'In Use'),
(2, 'MSEC-CSE-PC-002', 'Dell OptiPlex 7090 Desktop', 6, 'Dell', 'OptiPlex 7090', 'SN-DELL-887411', '00:1A:2B:3C:4D:5F', '192.168.10.102', '2027-08-30', 'Dell ProSupport Plus till Aug 2027', 'Intel Core i7 11th Gen, 16GB DDR4, 512GB NVMe SSD', '2024-09-01', 68000.00, 1, 2, 'AI Lab - Terminal 02', 12, 'In Use'),
(3, 'MSEC-CSE-SW-001', 'Cisco Catalyst 24-Port Switch', 6, 'Cisco', 'Catalyst 2960-X', 'SN-CSCO-998231', '00:25:9C:12:FE:AA', '192.168.10.254', '2025-12-31', 'Cisco SMARTnet Annual AMC Active', '24x 1G Ethernet + 4x 1G SFP, Managed Layer 2', '2022-03-15', 30000.00, 1, 3, 'Network Lab Server Rack A', 11, 'In Use'),
(4, 'MSEC-CSE-PRJ-01', 'Epson EB-E01 Projector', 6, 'Epson', 'EB-E01', 'SN-EPSN-334102', NULL, NULL, '2026-06-30', 'On-site lamp warranty till mid-2026', 'XGA, 3300 Lumens, HDMI/VGA', '2023-07-15', 38500.00, 1, 1, 'Programming Lab 201 Ceiling', 14, 'In Use'),
(5, 'MSEC-CSE-PC-003', 'Dell OptiPlex 7090 Desktop (SMPS Issue)', 6, 'Dell', 'OptiPlex 7090', 'SN-DELL-887412', '00:1A:2B:3C:4D:60', '192.168.10.103', '2027-08-30', 'Dell ProSupport Plus', 'Intel Core i7, 16GB DDR4', '2024-09-01', 68000.00, 1, 2, 'Central Store Service Bay', 6, 'Under Repair')
ON DUPLICATE KEY UPDATE asset_tag=VALUES(asset_tag);

-- Approval Workflows Configuration (Section 11.1 & 11.2)
INSERT INTO approval_workflows (id, workflow_name, level_number, level_title, approver_role, min_value, max_value, condition_notes) VALUES
(1, 'Institutional Procurement Workflow', 1, 'Department Stock Coordinator', 'dept_coordinator', 0.00, 99999999.00, 'Initial department-level technical and balance verification'),
(2, 'Institutional Procurement Workflow', 2, 'Head of Department (HOD)', 'hod', 0.00, 99999999.00, 'HOD departmental endorsement for institutional review'),
(3, 'Institutional Procurement Workflow', 3, 'Central Store Keeper', 'store_keeper', 0.00, 99999999.00, 'Store keeper stock verification & approval sheet generation'),
(4, 'Institutional Procurement Workflow', 4, 'Vice Principal', 'vice_principal', 0.00, 99999999.00, 'Administrative review and concurrence'),
(5, 'Institutional Procurement Workflow', 5, 'Principal', 'principal', 0.00, 99999999.00, 'Institutional academic review and recommendation to Management'),
(6, 'Institutional Procurement Workflow', 6, 'Director / Management', 'director', 0.00, 99999999.00, 'Ultimate financial sanction and budget disbursement by Management'),
(7, 'Institutional Procurement Workflow', 7, 'Central Store Keeper', 'store_keeper', 0.00, 99999999.00, 'Material fulfillment, PO generation, and physical issue')
ON DUPLICATE KEY UPDATE level_title=VALUES(level_title);

-- Vendors (Section 17)
INSERT INTO vendors (id, vendor_code, company_name, gst_number, contact_person, mobile_number, email, address, product_categories, performance_rating) VALUES
(1, 'VND-001', 'CompCare Solutions Pvt Ltd', '33AABCC1234F1Z5', 'M. Ananthan', '+91 94441 23456', 'sales@compcare.in', 'No. 45, Mount Road, Chennai - 600002', 'Desktops, Laptops, Servers, Networking', 4.8),
(2, 'VND-002', 'Sri Balaji Office Supplies', '33AAACB9876G2Z1', 'K. Balaji', '+91 98401 67890', 'balaji.supplies@gmail.com', 'No. 12, Usman Road, T. Nagar, Chennai - 600017', 'Stationery, A4 Sheets, Printer Toners', 4.6),
(3, 'VND-003', 'Apex Electrical & Instruments', '33AABDE5544H3Z8', 'R. Sridhar', '+91 97910 11223', 'apexinstruments@yahoo.co.in', 'Industrial Estate, Guindy, Chennai - 600032', 'Electrical Wires, Lab Meters, Oscilloscopes', 4.4)
ON DUPLICATE KEY UPDATE company_name=VALUES(company_name);

-- Stock Request Example from Section 10
INSERT INTO stock_requests (id, request_code, dept_id, lab_id, requested_by, purpose, required_date, total_estimated_value, current_level, status) VALUES
(1, 'CSE-REQ-2026-00125', 1, 2, 12, 'AI Laboratory Maintenance & Semester Lab Exam Preparation', '2026-10-09', 12350.00, 3, 'Pending Approval')
ON DUPLICATE KEY UPDATE request_code=VALUES(request_code);

INSERT INTO stock_request_items (id, request_id, item_id, requested_quantity, approved_quantity, unit_cost, notes) VALUES
(1, 1, 1, 10.00, 10.00, 350.00, 'HDMI Cable – 10 Nos for lab monitor displays'),
(2, 1, 2, 5.00, 5.00, 650.00, 'Keyboard – 5 Nos replacement for damaged terminals'),
(3, 1, 3, 20.00, 20.00, 280.00, 'A4 Sheets – 20 Reams for record notebooks & tests')
ON DUPLICATE KEY UPDATE requested_quantity=VALUES(requested_quantity);

-- Request Approvals for CSE-REQ-2026-00125
INSERT INTO request_approvals (request_id, level_number, level_title, approver_role, approver_user_id, status, comments, action_date) VALUES
(1, 1, 'Department Stock Coordinator', 'dept_coordinator', 11, 'Approved', 'Verified necessity for upcoming semester examinations in AI lab', '2026-09-20 10:15:00'),
(1, 2, 'HOD CSE', 'hod', 9, 'Approved', 'Approved from departmental quota. Recommended for store issue.', '2026-09-20 14:30:00'),
(1, 3, 'Vice Principal', 'vice_principal', 4, 'Pending', 'Awaiting administrative verification (Value ₹12,350)', NULL);

-- Stock Movements (Section 13: CSE to ECE Transfer Example)
INSERT INTO stock_movements (id, movement_code, movement_type, source_dept_id, target_dept_id, initiated_by, status, notes) VALUES
(1, 'MOV-2026-00042', 'DEPT_TO_DEPT', 1, 2, 11, 'In Transit', 'Inter-department transfer: CSE has 10 extra keyboards and ECE requested 5 units')
ON DUPLICATE KEY UPDATE movement_code=VALUES(movement_code);

INSERT INTO stock_movement_items (id, movement_id, item_id, quantity, remarks) VALUES
(1, 1, 2, 5.00, 'Dell USB Keyboards (CSE tech stock to ECE lab)')
ON DUPLICATE KEY UPDATE quantity=VALUES(quantity);

-- Activity Logs (Section 32)
INSERT INTO activity_logs (user_id, role, activity, module, action_details, ip_address, created_at) VALUES
(6, 'store_keeper', 'Issued 10 Keyboards', 'Stock Issue', 'Generated Issue Voucher VCH-2026-0089 for CSE Programming Lab', '192.168.1.50', '2026-09-01 10:30:00'),
(9, 'hod', 'Approved Request', 'Approval', 'HOD approved indent CSE-REQ-2026-00125 for AI Laboratory Maintenance', '192.168.10.5', '2026-09-01 14:45:00'),
(5, 'admin', 'Updated Item Master', 'Inventory', 'Adjusted reorder thresholds and safety parameters for A4 Sheets', '192.168.1.10', '2026-09-02 09:15:00')
ON DUPLICATE KEY UPDATE activity=VALUES(activity);
