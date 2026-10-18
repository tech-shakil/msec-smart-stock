<?php
/**
 * MSEC SMART STOCK - Unified Database Seeder & Migrator
 * Automatically provisions all tables, constraints, and enterprise seed data.
 */

require_once dirname(__DIR__) . '/config/app.php';

class DatabaseSeeder {

    public static function run(PDO $pdo, string $driver = 'sqlite') {
        self::createTables($pdo, $driver);
        self::seedData($pdo, $driver);
    }

    private static function createTables(PDO $pdo, string $driver) {
        $pk = ($driver === 'sqlite') ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT AUTO_INCREMENT PRIMARY KEY';
        $now = ($driver === 'sqlite') ? "DATETIME DEFAULT (datetime('now', 'localtime'))" : 'DATETIME DEFAULT CURRENT_TIMESTAMP';

        $queries = [
            // Organizations
            "CREATE TABLE IF NOT EXISTS organizations (
                id $pk,
                code VARCHAR(50) NOT NULL UNIQUE,
                name VARCHAR(255) NOT NULL,
                address TEXT,
                phone VARCHAR(50),
                email VARCHAR(100),
                established_year INT,
                created_at $now
            )",

            // Departments
            "CREATE TABLE IF NOT EXISTS departments (
                id $pk,
                org_id INT DEFAULT 1,
                code VARCHAR(50) NOT NULL UNIQUE,
                name VARCHAR(255) NOT NULL,
                hod_user_id INT NULL,
                created_at $now
            )",

            // Laboratories
            "CREATE TABLE IF NOT EXISTS laboratories (
                id $pk,
                dept_id INT NOT NULL,
                code VARCHAR(50) NOT NULL,
                name VARCHAR(255) NOT NULL,
                incharge_user_id INT NULL,
                location VARCHAR(255),
                created_at $now
            )",

            // Locations
            "CREATE TABLE IF NOT EXISTS locations (
                id $pk,
                building VARCHAR(100) NOT NULL,
                floor VARCHAR(50),
                room_number VARCHAR(50),
                description VARCHAR(255)
            )",

            // Stores
            "CREATE TABLE IF NOT EXISTS stores (
                id $pk,
                code VARCHAR(50) NOT NULL UNIQUE,
                name VARCHAR(255) NOT NULL,
                type VARCHAR(50) DEFAULT 'DEPARTMENT',
                dept_id INT NULL,
                keeper_user_id INT NULL,
                location_id INT NULL,
                created_at $now
            )",

            // Roles
            "CREATE TABLE IF NOT EXISTS roles (
                id $pk,
                role_key VARCHAR(50) NOT NULL UNIQUE,
                name VARCHAR(100) NOT NULL,
                level VARCHAR(100) NOT NULL,
                description TEXT
            )",

            // Users
            "CREATE TABLE IF NOT EXISTS users (
                id $pk,
                username VARCHAR(100) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                full_name VARCHAR(150) NOT NULL,
                email VARCHAR(150) NOT NULL UNIQUE,
                phone VARCHAR(50),
                employee_id VARCHAR(50),
                designation VARCHAR(150),
                role_key VARCHAR(50) NOT NULL,
                dept_id INT NULL,
                lab_id INT NULL,
                status VARCHAR(50) DEFAULT 'Active',
                failed_attempts INT DEFAULT 0,
                locked_until DATETIME NULL,
                last_login_at DATETIME NULL,
                last_login_ip VARCHAR(50) NULL,
                created_at $now
            )",

            // Item Categories
            "CREATE TABLE IF NOT EXISTS item_categories (
                id $pk,
                name VARCHAR(150) NOT NULL,
                type VARCHAR(50) NOT NULL,
                description TEXT
            )",

            // Units
            "CREATE TABLE IF NOT EXISTS units (
                id $pk,
                code VARCHAR(20) NOT NULL UNIQUE,
                name VARCHAR(100) NOT NULL
            )",

            // Items
            "CREATE TABLE IF NOT EXISTS items (
                id $pk,
                item_code VARCHAR(50) NOT NULL UNIQUE,
                item_name VARCHAR(255) NOT NULL,
                category_id INT NOT NULL,
                subcategory_id INT NULL,
                unit_id INT NOT NULL,
                is_consumable INT DEFAULT 1,
                specification TEXT NULL,
                manufacturer VARCHAR(150) NULL,
                model VARCHAR(150) NULL,
                min_stock NUMERIC DEFAULT 0,
                max_stock NUMERIC DEFAULT 1000,
                unit_cost NUMERIC DEFAULT 0,
                description TEXT,
                created_at $now
            )",

            // Stock
            "CREATE TABLE IF NOT EXISTS stock (
                id $pk,
                store_id INT NOT NULL,
                item_id INT NOT NULL,
                available_quantity NUMERIC DEFAULT 0,
                reserved_quantity NUMERIC DEFAULT 0,
                store_location VARCHAR(100),
                last_movement_date VARCHAR(50) NULL,
                updated_at $now
            )",

            // Stock Requests
            "CREATE TABLE IF NOT EXISTS stock_requests (
                id $pk,
                request_code VARCHAR(50) NOT NULL UNIQUE,
                request_type VARCHAR(50) DEFAULT 'NEW_PURCHASE',
                priority VARCHAR(20) DEFAULT 'Normal',
                dept_id INT NOT NULL,
                lab_id INT NULL,
                requested_by INT NOT NULL,
                purpose TEXT NOT NULL,
                required_date VARCHAR(50) NOT NULL,
                total_estimated_value NUMERIC DEFAULT 0,
                current_level INT DEFAULT 1,
                status VARCHAR(50) DEFAULT 'Pending Approval',
                created_at $now
            )",

            // Stock Request Items
            "CREATE TABLE IF NOT EXISTS stock_request_items (
                id $pk,
                request_id INT NOT NULL,
                item_id INT NOT NULL,
                requested_quantity NUMERIC NOT NULL,
                approved_quantity NUMERIC DEFAULT 0,
                unit_cost NUMERIC DEFAULT 0,
                notes VARCHAR(255)
            )",

            // Approval Workflows
            "CREATE TABLE IF NOT EXISTS approval_workflows (
                id $pk,
                workflow_name VARCHAR(150) NOT NULL,
                level_number INT NOT NULL,
                level_title VARCHAR(100) NOT NULL,
                approver_role VARCHAR(50) NOT NULL,
                min_value NUMERIC DEFAULT 0,
                max_value NUMERIC DEFAULT 99999999,
                condition_notes TEXT
            )",

            // Request Approvals
            "CREATE TABLE IF NOT EXISTS request_approvals (
                id $pk,
                request_id INT NOT NULL,
                level_number INT NOT NULL,
                level_title VARCHAR(100) NOT NULL,
                approver_role VARCHAR(50) NOT NULL,
                approver_user_id INT NULL,
                status VARCHAR(50) DEFAULT 'Pending',
                comments TEXT,
                action_date VARCHAR(50) NULL
            )",

            // Official Store Keeper Approval Sheets
            "CREATE TABLE IF NOT EXISTS approval_sheets (
                id $pk,
                sheet_number VARCHAR(50) NOT NULL UNIQUE,
                request_id INT NOT NULL,
                store_keeper_id INT NOT NULL,
                existing_stock_notes TEXT,
                spec_verification_notes TEXT,
                vendor_id INT NULL,
                quotation_ref VARCHAR(100),
                item_price NUMERIC DEFAULT 0,
                gst_percent NUMERIC DEFAULT 18,
                total_cost NUMERIC DEFAULT 0,
                recommendation TEXT,
                created_at $now
            )",

            // Stock Issues & Vouchers
            "CREATE TABLE IF NOT EXISTS stock_issues (
                id $pk,
                issue_code VARCHAR(50) NOT NULL UNIQUE,
                request_id INT NULL,
                source_store_id INT NOT NULL,
                target_dept_id INT NOT NULL,
                target_lab_id INT NULL,
                issued_by INT NOT NULL,
                recipient_id INT NULL,
                issue_date $now,
                voucher_type VARCHAR(50) DEFAULT 'Issue Voucher',
                status VARCHAR(50) DEFAULT 'Issued',
                remarks TEXT
            )",

            // Stock Issue Items
            "CREATE TABLE IF NOT EXISTS stock_issue_items (
                id $pk,
                issue_id INT NOT NULL,
                item_id INT NOT NULL,
                quantity NUMERIC NOT NULL,
                notes VARCHAR(255)
            )",

            // Department Receipts
            "CREATE TABLE IF NOT EXISTS department_receipts (
                id $pk,
                receipt_code VARCHAR(50) NOT NULL UNIQUE,
                issue_id INT NOT NULL,
                dept_id INT NOT NULL,
                acknowledged_by INT NOT NULL,
                acknowledgement_date $now,
                digital_acknowledgement INT DEFAULT 1,
                remarks TEXT
            )",

            // Stock Movements
            "CREATE TABLE IF NOT EXISTS stock_movements (
                id $pk,
                movement_code VARCHAR(50) NOT NULL UNIQUE,
                movement_type VARCHAR(50) NOT NULL,
                source_dept_id INT NULL,
                target_dept_id INT NULL,
                source_lab_id INT NULL,
                target_lab_id INT NULL,
                initiated_by INT NOT NULL,
                status VARCHAR(50) DEFAULT 'Pending Approval',
                notes TEXT,
                created_at $now
            )",

            // Stock Movement Items
            "CREATE TABLE IF NOT EXISTS stock_movement_items (
                id $pk,
                movement_id INT NOT NULL,
                item_id INT NOT NULL,
                quantity NUMERIC NOT NULL,
                remarks VARCHAR(255)
            )",

            // Assets
            "CREATE TABLE IF NOT EXISTS assets (
                id $pk,
                asset_id VARCHAR(50) NOT NULL UNIQUE,
                asset_name VARCHAR(255) NOT NULL,
                category_id INT NOT NULL,
                brand VARCHAR(100),
                model VARCHAR(100),
                serial_number VARCHAR(100),
                mac_address VARCHAR(50),
                ip_address VARCHAR(50),
                warranty_expiry VARCHAR(50),
                amc_details TEXT,
                configuration TEXT,
                purchase_date VARCHAR(50),
                purchase_cost NUMERIC DEFAULT 0,
                dept_id INT NOT NULL,
                lab_id INT NULL,
                location VARCHAR(100),
                custodian_id INT NULL,
                asset_status VARCHAR(50) DEFAULT 'Available',
                created_at $now
            )",

            // Asset Maintenance
            "CREATE TABLE IF NOT EXISTS asset_maintenance (
                id $pk,
                ticket_number VARCHAR(50) NOT NULL UNIQUE,
                asset_id INT NOT NULL,
                reported_by INT NOT NULL,
                technician_id INT NULL,
                issue_reported TEXT NOT NULL,
                diagnosis TEXT,
                status VARCHAR(50) DEFAULT 'Open',
                repair_cost NUMERIC DEFAULT 0,
                resolution_notes TEXT,
                completed_date VARCHAR(50) NULL,
                created_at $now
            )",

            // Vendors
            "CREATE TABLE IF NOT EXISTS vendors (
                id $pk,
                vendor_code VARCHAR(50) NOT NULL UNIQUE,
                company_name VARCHAR(255) NOT NULL,
                gst_number VARCHAR(50),
                pan_number VARCHAR(50),
                contact_person VARCHAR(100),
                mobile_number VARCHAR(50),
                email VARCHAR(100),
                address TEXT,
                product_categories TEXT,
                performance_rating NUMERIC DEFAULT 4.5,
                status VARCHAR(20) DEFAULT 'Active',
                created_at $now
            )",

            // Quotations
            "CREATE TABLE IF NOT EXISTS quotations (
                id $pk,
                request_id INT NULL,
                vendor_id INT NOT NULL,
                quote_reference VARCHAR(100) NOT NULL,
                quote_date VARCHAR(50) NOT NULL,
                total_amount NUMERIC NOT NULL,
                tax_amount NUMERIC DEFAULT 0,
                delivery_period VARCHAR(50),
                warranty_terms VARCHAR(100),
                is_selected INT DEFAULT 0,
                remarks TEXT,
                created_at $now
            )",

            // Purchase Orders
            "CREATE TABLE IF NOT EXISTS purchase_orders (
                id $pk,
                po_number VARCHAR(50) NOT NULL UNIQUE,
                indent_id INT NULL,
                vendor_id INT NOT NULL,
                total_amount NUMERIC DEFAULT 0,
                tax_amount NUMERIC DEFAULT 0,
                terms TEXT,
                status VARCHAR(50) DEFAULT 'Issued',
                created_by INT NULL,
                created_at $now
            )",

            // Goods Receipts (GRN)
            "CREATE TABLE IF NOT EXISTS goods_receipts (
                id $pk,
                grn_number VARCHAR(50) NOT NULL UNIQUE,
                po_id INT NULL,
                vendor_id INT NOT NULL,
                store_id INT NOT NULL,
                received_date VARCHAR(50) NOT NULL,
                verified_by INT NOT NULL,
                invoice_no VARCHAR(100),
                invoice_date VARCHAR(50),
                remarks TEXT,
                created_at $now
            )",

            // Goods Receipt Items
            "CREATE TABLE IF NOT EXISTS goods_receipt_items (
                id $pk,
                grn_id INT NOT NULL,
                item_id INT NOT NULL,
                ordered_qty NUMERIC NOT NULL,
                received_qty NUMERIC NOT NULL,
                accepted_qty NUMERIC NOT NULL,
                rejected_qty NUMERIC DEFAULT 0,
                unit_price NUMERIC NOT NULL
            )",

            // Budgets
            "CREATE TABLE IF NOT EXISTS budgets (
                id $pk,
                dept_id INT NOT NULL,
                budget_head_code VARCHAR(100) NOT NULL UNIQUE,
                academic_year VARCHAR(20) NOT NULL,
                category VARCHAR(50) DEFAULT 'OPERATIONAL',
                allocated_amount NUMERIC DEFAULT 0,
                utilized_amount NUMERIC DEFAULT 0,
                committed_amount NUMERIC DEFAULT 0,
                created_at $now
            )",

            // Notifications
            "CREATE TABLE IF NOT EXISTS notifications (
                id $pk,
                user_id INT NULL,
                role_key VARCHAR(50) NULL,
                type VARCHAR(50) NOT NULL,
                title VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                link_url VARCHAR(255) NULL,
                is_read INT DEFAULT 0,
                created_at $now
            )",

            // Permanent Immutable Audit Logs
            "CREATE TABLE IF NOT EXISTS audit_logs (
                id $pk,
                user_id INT NULL,
                role VARCHAR(50),
                action VARCHAR(100) NOT NULL,
                module VARCHAR(100) NOT NULL,
                entity_type VARCHAR(100) NULL,
                entity_id INT NULL,
                old_values TEXT NULL,
                new_values TEXT NULL,
                remarks TEXT NULL,
                ip_address VARCHAR(50),
                created_at $now
            )",

            // Stock Verifications
            "CREATE TABLE IF NOT EXISTS stock_verifications (
                id $pk,
                verification_code VARCHAR(50) NOT NULL UNIQUE,
                store_id INT NOT NULL,
                verification_type VARCHAR(50) DEFAULT 'Monthly',
                conducted_by INT NOT NULL,
                verified_date VARCHAR(50) NOT NULL,
                status VARCHAR(50) DEFAULT 'Completed',
                remarks TEXT,
                created_at $now
            )",

            // System Settings
            "CREATE TABLE IF NOT EXISTS system_settings (
                setting_key VARCHAR(100) PRIMARY KEY,
                setting_value TEXT NOT NULL,
                category VARCHAR(50) DEFAULT 'General',
                description TEXT
            )"
        ];

        foreach ($queries as $sql) {
            $pdo->exec($sql);
        }
    }

    private static function seedData(PDO $pdo, string $driver) {
        $check = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        if ($check > 0) {
            return;
        }

        // Organizations
        $pdo->exec("INSERT INTO organizations (id, code, name, address, phone, email, established_year) VALUES
            (1, 'MSEC', 'Mohamed Sathak Engineering College', 'Kilakarai - 623 806, Ramanathapuram Dist., Tamil Nadu', '+91 4567 241327', 'stock@msec.org.in', 1984)");

        // Departments (All Academic & Central Departments)
        $pdo->exec("INSERT INTO departments (id, org_id, code, name) VALUES
            (1, 1, 'CSE', 'Computer Science & Engineering'),
            (2, 1, 'ECE', 'Electronics & Communication Engineering'),
            (3, 1, 'EEE', 'Electrical & Electronics Engineering'),
            (4, 1, 'MECH', 'Mechanical Engineering'),
            (5, 1, 'CIVIL', 'Civil Engineering'),
            (6, 1, 'ADMIN_DEPT', 'Central College Administration'),
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
            (17, 1, 'PHYS', 'Physical Education & Sports Department')");

        // Laboratories & Facilities
        $pdo->exec("INSERT INTO laboratories (id, dept_id, code, name, location) VALUES
            (1, 1, 'CSE-LINUX', 'Linux Laboratory', 'Main Block - Floor 2 - Lab 201'),
            (2, 1, 'CSE-CASE', 'CASE Tools Laboratory', 'Main Block - Floor 3 - Lab 304'),
            (3, 1, 'CSE-ANDROID', 'Android Laboratory', 'Main Block - Floor 2 - Lab 205'),
            (5, 2, 'ECE-COMM', 'Communication Systems Lab', 'Science Block - Floor 1 - Lab 102'),
            (6, 3, 'EEE-POW', 'Power Electronics Lab', 'Science Block - Floor 2 - Lab 201'),
            (7, 4, 'MECH-CAD', 'CAD / CAM Automation Center', 'Workshop Block - Floor 1 - Lab 101'),
            (8, 14, 'LIB-MAIN', 'Central Library Reading & Book Stacks', 'Library Block - Ground Floor'),
            (9, 14, 'LIB-DIGI', 'Digital Library & Online Resource Center', 'Library Block - Floor 1 - Lab 101'),
            (10, 15, 'INC-MAKER', 'MSEC Incubation & Makerspace Center', 'Tech Park - Floor 1 - Bay A'),
            (11, 15, 'INC-IOT', 'IoT Prototyping & Hardware Testing Lab', 'Tech Park - Floor 1 - Lab 102')");

        // Stores
        $pdo->exec("INSERT INTO stores (id, code, name, type, dept_id, keeper_user_id) VALUES
            (1, 'STORE-CENTRAL', 'MSEC Central Main Store', 'CENTRAL', NULL, 6),
            (2, 'STORE-CSE', 'CSE Department Sub-Store', 'DEPARTMENT', 1, 11),
            (3, 'STORE-ECE', 'ECE Department Sub-Store', 'DEPARTMENT', 2, 10),
            (4, 'STORE-EEE', 'EEE Department Sub-Store', 'DEPARTMENT', 3, NULL),
            (5, 'STORE-MECH', 'Mechanical Tool Crib Store', 'DEPARTMENT', 4, NULL),
            (6, 'STORE-CIVIL', 'Civil Material Store', 'DEPARTMENT', 5, NULL)");

        // Roles
        foreach (ROLES as $role_key => $info) {
            $stmt = $pdo->prepare("INSERT INTO roles (role_key, name, level, description) VALUES (?, ?, ?, ?)");
            $stmt->execute([$role_key, $info['name'], $info['level'], "Institutional authorization role for {$info['name']}"]);
        }

        // Default password hash for 'password123'
        $passHash = password_hash('password123', PASSWORD_BCRYPT);

        // Institutional Users across all Roles
        $users = [
            [1, 'superadmin', $passHash, 'Engr. M. Shakil (System Admin)', 'admin@msec.edu.in', '+91 9876543210', 'EMP-SYS-001', 'Chief System Administrator', 'super_admin', 6, null],
            [2, 'director', $passHash, 'Dr. K. S. Babai', 'director@msec.edu.in', '+91 9876543211', 'EMP-DIR-001', 'Director (Trust Governance)', 'director', 6, null],
            [3, 'principal', $passHash, 'Dr. C. Umarani', 'principal@msec.edu.in', '+91 9876543212', 'EMP-PRIN-001', 'Principal', 'principal', 6, null],
            [4, 'viceprincipal', $passHash, 'Dr. S. Rajendran', 'vp@msec.edu.in', '+91 9876543213', 'EMP-VP-001', 'Vice Principal', 'vice_principal', 6, null],
            [5, 'viewer', $passHash, 'Janab Alhaj S.M. Yousuf', 'viewer@msec.edu.in', '+91 9876543214', 'EMP-TRU-001', 'Governing Body Observer', 'management_viewer', 6, null],
            [6, 'storekeeper', $passHash, 'K. Ramasamy', 'store@msec.edu.in', '+91 9876543215', 'EMP-STR-001', 'Central Store Keeper (Gateway)', 'store_keeper', 6, null],
            [7, 'purchaseofficer', $passHash, 'P. Natarajan', 'purchase@msec.edu.in', '+91 9876543216', 'EMP-PUR-001', 'Procurement & Purchase Officer', 'purchase_officer', 6, null],
            [8, 'accountsofficer', $passHash, 'A. Mohamed Hameed', 'accounts@msec.edu.in', '+91 9876543217', 'EMP-ACC-001', 'Chief Accounts Officer', 'accounts_officer', 6, null],
            [9, 'hod_cse', $passHash, 'Mr. N. Balasubramanian', 'csehod@msec.org.in', '+91 94431 12345', 'EMP-CSE-001', 'Associate Professor & Head of Department (CSE)', 'hod', 1, null],
            [10, 'coord_cse', $passHash, 'Mr. K. Seeni Pulavar Pitchai', 'seenipulavar2025@gmail.com', '+91 98421 67890', 'EMP-CSE-002', 'Assistant Professor & Department Stock Coordinator', 'dept_coordinator', 1, null],
            [11, 'merlin_rithika', $passHash, 'Ms. M.K. Merlin Rithika', 'merlinrithika21@gmail.com', '+91 97890 12345', 'EMP-CSE-003', 'Assistant Professor & Lab In-charge (Linux & CASE Tools)', 'lab_incharge', 1, 1],
            [13, 'tech_cse', $passHash, 'T. Murugan', 'tech.cse@msec.edu.in', '+91 9876543223', 'EMP-TEC-003', 'Senior Maintenance Technician', 'technician', 1, 1],
            [14, 'auditor', $passHash, 'CMA S. Narayanan', 'audit@msec.edu.in', '+91 9876543224', 'EMP-AUD-001', 'Statutory Internal Auditor', 'auditor', 6, null],
            [15, 'mangala_jothi', $passHash, 'Ms. C. Mangala Jothi', 'Jothichandran1994@gmail.com', '+91 98940 54321', 'EMP-CSE-004', 'Assistant Professor & Lab In-charge (Android)', 'lab_incharge', 1, 3],

            // IT Department (Dept 7)
            [16, 'hod_it', $passHash, 'Dr. K. Karthikeyan', 'ithod@msec.org.in', '+91 94431 23456', 'EMP-IT-001', 'Associate Professor & Head of Department (IT)', 'hod', 7, null],
            [17, 'coord_it', $passHash, 'Er. M. Naveen Kumar', 'itcoord@msec.org.in', '+91 98421 23456', 'EMP-IT-002', 'Assistant Professor & IT Stock Coordinator', 'dept_coordinator', 7, null],
            [18, 'incharge_it', $passHash, 'Mrs. P. Priya', 'itlab@msec.org.in', '+91 97890 23456', 'EMP-IT-003', 'Assistant Professor & Lab In-charge (Network Security)', 'lab_incharge', 7, 12],

            // AI & Data Science (Dept 8)
            [19, 'hod_aids', $passHash, 'Dr. V. Senthil Murugan', 'aidshod@msec.org.in', '+91 94431 34567', 'EMP-AIDS-001', 'Associate Professor & Head of Department (AI&DS)', 'hod', 8, null],
            [20, 'coord_aids', $passHash, 'Er. R. Vignesh', 'aidscoord@msec.org.in', '+91 98421 34567', 'EMP-AIDS-002', 'Assistant Professor & AI&DS Stock Coordinator', 'dept_coordinator', 8, null],
            [21, 'incharge_aids', $passHash, 'Mr. S. Dinesh', 'aidslab@msec.org.in', '+91 97890 34567', 'EMP-AIDS-003', 'Assistant Professor & Lab In-charge (Deep Learning)', 'lab_incharge', 8, 13],

            // ECE Department (Dept 2)
            [22, 'hod_ece', $passHash, 'Dr. S. Syed Abuthahir', 'ecehod@msec.org.in', '+91 94431 45678', 'EMP-ECE-001', 'Professor & Head of Department (ECE)', 'hod', 2, null],
            [23, 'coord_ece', $passHash, 'Er. K. Ramesh', 'ececoord@msec.org.in', '+91 98421 45678', 'EMP-ECE-002', 'Assistant Professor & ECE Stock Coordinator', 'dept_coordinator', 2, null],
            [24, 'incharge_ece', $passHash, 'Mrs. M. Kavitha', 'ecelab@msec.org.in', '+91 97890 45678', 'EMP-ECE-003', 'Assistant Professor & Lab In-charge (Comm Systems)', 'lab_incharge', 2, 5],

            // EEE Department (Dept 3)
            [25, 'hod_eee', $passHash, 'Dr. A. Raja', 'eeehod@msec.org.in', '+91 94431 56789', 'EMP-EEE-001', 'Professor & Head of Department (EEE)', 'hod', 3, null],
            [26, 'coord_eee', $passHash, 'Er. S. Manikandan', 'eeecoord@msec.org.in', '+91 98421 56789', 'EMP-EEE-002', 'Assistant Professor & EEE Stock Coordinator', 'dept_coordinator', 3, null],
            [27, 'incharge_eee', $passHash, 'Mr. B. Suresh', 'eeelab@msec.org.in', '+91 97890 56789', 'EMP-EEE-003', 'Assistant Professor & Lab In-charge (Power Electronics)', 'lab_incharge', 3, 6],

            // MECH Department (Dept 4)
            [28, 'hod_mech', $passHash, 'Dr. M. Ganesan', 'mechhod@msec.org.in', '+91 94431 67890', 'EMP-MECH-001', 'Professor & Head of Department (MECH)', 'hod', 4, null],
            [29, 'coord_mech', $passHash, 'Er. P. Vijayakumar', 'mechcoord@msec.org.in', '+91 98421 67890', 'EMP-MECH-002', 'Assistant Professor & MECH Stock Coordinator', 'dept_coordinator', 4, null],
            [30, 'incharge_mech', $passHash, 'Mr. R. Anand', 'mechlab@msec.org.in', '+91 97890 67890', 'EMP-MECH-003', 'Assistant Professor & Lab In-charge (CAD/CAM Center)', 'lab_incharge', 4, 7],

            // CIVIL Department (Dept 5)
            [31, 'hod_civil', $passHash, 'Dr. R. Subramanian', 'civilhod@msec.org.in', '+91 94431 78901', 'EMP-CIV-001', 'Professor & Head of Department (CIVIL)', 'hod', 5, null],
            [32, 'coord_civil', $passHash, 'Er. G. Selvaraj', 'civilcoord@msec.org.in', '+91 98421 78901', 'EMP-CIV-002', 'Assistant Professor & CIVIL Stock Coordinator', 'dept_coordinator', 5, null],
            [33, 'incharge_civil', $passHash, 'Mr. K. Rajesh', 'civillab@msec.org.in', '+91 97890 78901', 'EMP-CIV-003', 'Assistant Professor & Lab In-charge (Materials Lab)', 'lab_incharge', 5, null],

            // Central Library (Dept 14)
            [34, 'chief_librarian', $passHash, 'Dr. N. Mohamed Ismail', 'librarian@msec.edu.in', '+91 94431 89012', 'EMP-LIB-001', 'Chief Librarian & Head of Central Library', 'hod', 14, 8],
            [35, 'lib_incharge', $passHash, 'Mrs. J. Fathima', 'library@msec.edu.in', '+91 98421 89012', 'EMP-LIB-002', 'Assistant Librarian (Circulation & Issues)', 'lab_incharge', 14, 8],
            [36, 'digitallib_incharge', $passHash, 'Mr. A. Sikkandar', 'digitallib@msec.edu.in', '+91 97890 89012', 'EMP-LIB-003', 'Digital Resource In-charge (120 Terminals)', 'lab_incharge', 14, 9],

            // MSEC Incubation & Innovation Hub (Dept 15)
            [37, 'incubation_head', $passHash, 'Dr. T. Sheikh Fakrudeen', 'incubation@msec.edu.in', '+91 94431 90123', 'EMP-INC-001', 'Director (Incubation & EDII Hub)', 'hod', 15, 10],
            [38, 'maker_incharge', $passHash, 'Er. K. Mohamed Rifath', 'maker@msec.edu.in', '+91 98421 90123', 'EMP-INC-002', 'IoT & Makerspace Lab In-charge', 'lab_incharge', 15, 11]
        ];

        $stmtUser = $pdo->prepare("INSERT INTO users (id, username, password_hash, full_name, email, phone, employee_id, designation, role_key, dept_id, lab_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Active')");
        foreach ($users as $u) {
            $stmtUser->execute($u);
        }

        // Units
        $pdo->exec("INSERT INTO units (id, code, name) VALUES
            (1, 'Nos', 'Numbers / Units'),
            (2, 'Reams', 'Paper Reams'),
            (3, 'Box', 'Box / Pack'),
            (4, 'Meters', 'Meters'),
            (5, 'Kg', 'Kilograms'),
            (6, 'Litre', 'Litres'),
            (7, 'Set', 'Set')");

        // Categories
        $pdo->exec("INSERT INTO item_categories (id, name, type, description) VALUES
            (1, 'Stationery & Paper', 'CONSUMABLE', 'A4 sheets, registers, pens, office papers'),
            (2, 'IT Consumables', 'CONSUMABLE', 'Printer toner cartridges, network cables, patch cords'),
            (3, 'Electrical & Wiring', 'CONSUMABLE', 'Electrical copper wire, fuses, switches, LED tubes'),
            (4, 'Electronic Components', 'CONSUMABLE', 'Breadboards, microcontrollers, resistors, sensors'),
            (5, 'Lab Chemicals & Reagents', 'CONSUMABLE', 'Acids, solvents, analytical grade reagents'),
            (6, 'IT Equipment & Hardware', 'NON_CONSUMABLE', 'High performance desktop workstations, servers, core switches'),
            (7, 'Lab Instruments & Machinery', 'NON_CONSUMABLE', 'Digital storage oscilloscopes, signal generators'),
            (8, 'Furniture & Fixtures', 'NON_CONSUMABLE', 'Ergonomic lab chairs, staff desks, seminar tables')");

        // Items
        $pdo->exec("INSERT INTO items (id, item_code, item_name, category_id, unit_id, is_consumable, specification, manufacturer, model, min_stock, max_stock, unit_cost, description) VALUES
            (1, 'IT-CBL-001', 'HDMI Cable 2.0 (3 Meter)', 2, 1, 1, 'Gold-plated 4K 60Hz 18Gbps', 'AmazonBasics', 'AB-HD-3M', 15, 100, 350, 'High speed HDMI cable for smart classrooms and labs'),
            (2, 'IT-KEY-002', 'Dell USB Multimedia Keyboard', 6, 1, 0, '104-key USB wired spill-resistant', 'Dell', 'KB216', 10, 200, 650, 'Standard laboratory student desktop keyboards'),
            (3, 'STN-PPR-001', 'A4 Copier Sheets (75 GSM, 500 Sheets)', 1, 2, 1, '75 GSM high-brightness copier paper', 'JK Paper', 'JK-Copier-A4', 25, 500, 280, 'Standard institutional printing and examination sheets'),
            (4, 'IT-TNR-001', 'HP LaserJet 88A Toner Cartridge', 2, 1, 1, 'High yield black toner cartridge', 'HP', 'CC388A', 5, 40, 1450, 'Genuine toner cartridge for department laser printers'),
            (5, 'ELC-WIR-001', 'Copper Wire 1.5 sq mm (90m Roll)', 3, 4, 1, 'FR PVC insulated building copper wire', 'Finolex', 'FN-1.5-90', 10, 100, 1250, 'Standard electrical maintenance wire roll'),
            (6, 'CHM-SLN-001', 'Hydrochloric Acid GR 37% (2.5L)', 5, 6, 1, 'Analytical grade 37% pure HCl', 'Merck', 'GR-HCL-2.5', 4, 20, 850, 'Chemical analysis reagent bottle'),
            (7, 'IT-SYS-001', 'Dell OptiPlex 7090 Desktop (i7, 16GB, 512GB)', 6, 1, 0, 'Core i7 11th Gen, 16GB DDR4, 512GB NVMe SSD', 'Dell', 'OptiPlex 7090 MT', 5, 150, 68000, 'Workstation for AI & machine learning laboratories'),
            (8, 'IT-SWT-001', 'Cisco Catalyst 24-Port Managed Switch', 6, 1, 0, '24x 1Gbps PoE+ ports, Managed Layer 2/3', 'Cisco', 'WS-C2960X-24PS-L', 2, 30, 30000, 'Campus network backbone access switch'),
            (9, 'IT-PRJ-001', 'Epson EB-E01 XGA 3300 Lumens Projector', 6, 1, 0, '3300 Lumens, HDMI, XGA resolution', 'Epson', 'EB-E01', 3, 50, 38500, 'Classroom and seminar hall presentation projector'),
            (10, 'IT-KEY-OLD', 'Legacy PS/2 Keyboard (Dead Stock)', 6, 1, 0, 'PS/2 Connector, Non-USB', 'Intex', 'IT-KB-01', 5, 50, 500, 'Obsolete keyboard stock inactive since 2023 - Dead Stock')");

        // Stock Balances
        $pdo->exec("INSERT INTO stock (store_id, item_id, available_quantity, reserved_quantity, store_location, last_movement_date) VALUES
            (1, 1, 65, 10, 'Central Rack B-04', '2026-09-15'),
            (1, 2, 45, 5, 'Central Rack IT-01', '2026-09-18'),
            (1, 3, 10, 0, 'Central Paper Bay A1', '2026-09-10'),
            (1, 4, 18, 2, 'Central Rack IT-03', '2026-09-12'),
            (1, 7, 24, 0, 'Central Secure Vault', '2026-09-01'),
            (1, 8, 3, 0, 'Central Network Bay', '2022-11-20'),
            (1, 10, 15, 0, 'Central Scrap Shelf C', '2023-04-10'),
            (2, 1, 12, 0, 'CSE Lab Cabinet A', '2026-09-12'),
            (2, 2, 20, 0, 'CSE Tech Store Rack 1', '2026-09-15'),
            (3, 2, 2, 0, 'ECE Component Lab', '2026-08-20')");

        // Assets
        $pdo->exec("INSERT INTO assets (id, asset_id, asset_name, category_id, brand, model, serial_number, mac_address, ip_address, warranty_expiry, amc_details, configuration, purchase_date, purchase_cost, dept_id, lab_id, location, custodian_id, asset_status) VALUES
            (1, 'MSEC-CSE-PC-001', 'Dell OptiPlex 7090 Desktop', 6, 'Dell', 'OptiPlex 7090', 'SN-DELL-887410', '00:1A:2B:3C:4D:5E', '192.168.10.101', '2027-08-30', 'Dell ProSupport Plus till Aug 2027', 'Core i7 11th Gen, 16GB DDR4, 512GB SSD', '2024-09-01', 68000, 1, 2, 'AI Lab - Terminal 01', 11, 'In Use'),
            (2, 'MSEC-CSE-PC-002', 'Dell OptiPlex 7090 Desktop', 6, 'Dell', 'OptiPlex 7090', 'SN-DELL-887411', '00:1A:2B:3C:4D:5F', '192.168.10.102', '2027-08-30', 'Dell ProSupport Plus till Aug 2027', 'Core i7 11th Gen, 16GB DDR4, 512GB SSD', '2024-09-01', 68000, 1, 2, 'AI Lab - Terminal 02', 11, 'In Use'),
            (3, 'MSEC-CSE-SW-001', 'Cisco Catalyst 24-Port Switch', 6, 'Cisco', 'Catalyst 2960-X', 'SN-CSCO-998231', '00:25:9C:12:FE:AA', '192.168.10.254', '2025-12-31', 'Cisco SMARTnet Annual AMC Active', '24x 1G Ethernet + 4x 1G SFP', '2022-03-15', 30000, 1, 3, 'Network Lab Server Rack A', 10, 'In Use'),
            (4, 'MSEC-CSE-PRJ-01', 'Epson EB-E01 Projector', 6, 'Epson', 'EB-E01', 'SN-EPSN-334102', NULL, NULL, '2026-06-30', 'On-site lamp warranty till mid-2026', 'XGA, 3300 Lumens', '2023-07-15', 38500, 1, 1, 'Programming Lab 201 Ceiling', 13, 'In Use'),
            (5, 'MSEC-CSE-PC-003', 'Dell OptiPlex 7090 Desktop (SMPS Fault)', 6, 'Dell', 'OptiPlex 7090', 'SN-DELL-887412', '00:1A:2B:3C:4D:60', '192.168.10.103', '2027-08-30', 'Dell ProSupport Plus', 'Core i7, 16GB DDR4', '2024-09-01', 68000, 1, 2, 'Central Service Bay', 6, 'Under Repair')");

        // Asset Maintenance Tickets
        $pdo->exec("INSERT INTO asset_maintenance (id, ticket_number, asset_id, reported_by, technician_id, issue_reported, diagnosis, status, repair_cost, resolution_notes) VALUES
            (1, 'TCK-2026-0001', 5, 11, 13, 'Terminal will not power on; burnt odor from SMPS unit', 'SMPS capacitor blown due to voltage surge. Replacement 300W SMPS unit required.', 'Under Repair', 2200, 'Replacement SMPS indented from approved Dell vendor.')");

        // Vendors
        $pdo->exec("INSERT INTO vendors (id, vendor_code, company_name, gst_number, pan_number, contact_person, mobile_number, email, address, product_categories, performance_rating, status) VALUES
            (1, 'VND-001', 'CompCare Solutions Pvt Ltd', '33AABCC1234F1Z5', 'AABCC1234F', 'M. Ananthan', '+91 94441 23456', 'sales@compcare.in', 'No. 45, Mount Road, Chennai - 600002', 'Desktops, Laptops, Servers, Networking', 4.8, 'Active'),
            (2, 'VND-002', 'Sri Balaji Office Supplies', '33AAACB9876G2Z1', 'AAACB9876G', 'K. Balaji', '+91 98401 67890', 'balaji.supplies@gmail.com', 'No. 12, Usman Road, T. Nagar, Chennai - 600017', 'Stationery, A4 Sheets, Printer Toners', 4.6, 'Active'),
            (3, 'VND-003', 'Apex Electrical & Instruments', '33AABDE5544H3Z8', 'AABDE5544H', 'R. Sridhar', '+91 97910 11223', 'apexinstruments@yahoo.co.in', 'Industrial Estate, Guindy, Chennai - 600032', 'Electrical Wires, Lab Meters, Oscilloscopes', 4.4, 'Active')");

        // Budgets (Annual Department Allocations)
        $pdo->exec("INSERT INTO budgets (id, dept_id, budget_head_code, academic_year, category, allocated_amount, utilized_amount, committed_amount) VALUES
            (1, 1, 'MSEC-BUD-2026-CSE-CAP', '2026-2027', 'CAPITAL', 1500000, 480000, 120000),
            (2, 1, 'MSEC-BUD-2026-CSE-OPX', '2026-2027', 'OPERATIONAL', 350000, 145000, 25000),
            (3, 2, 'MSEC-BUD-2026-ECE-CAP', '2026-2027', 'CAPITAL', 1200000, 320000, 50000),
            (4, 4, 'MSEC-BUD-2026-MECH-OPX', '2026-2027', 'OPERATIONAL', 400000, 210000, 30000)");

        // Quotations
        $pdo->exec("INSERT INTO quotations (id, request_id, vendor_id, quote_reference, quote_date, total_amount, tax_amount, delivery_period, warranty_terms, is_selected, remarks) VALUES
            (1, 1, 1, 'QT-COMP-2026-089', '2026-09-18', 12350, 1884, '7 Days', '1 Year Standard On-site', 1, 'Lowest quoted price meeting technical specifications exactly'),
            (2, 1, 2, 'QT-BALA-2026-441', '2026-09-19', 13100, 1998, '10 Days', '1 Year Standard', 0, 'Quotation slightly higher than CompCare Solutions')");

        // Stock Requisition Example
        $pdo->exec("INSERT INTO stock_requests (id, request_code, request_type, priority, dept_id, lab_id, requested_by, purpose, required_date, total_estimated_value, current_level, status) VALUES
            (1, 'CSE-REQ-2026-00125', 'NEW_PURCHASE', 'High', 1, 2, 11, 'AI Laboratory Maintenance & Semester Lab Exam Preparation', '2026-10-09', 12350, 4, 'STORE_VERIFIED')");

        $pdo->exec("INSERT INTO stock_request_items (id, request_id, item_id, requested_quantity, approved_quantity, unit_cost, notes) VALUES
            (1, 1, 1, 10, 10, 350, 'HDMI Cable – 10 Nos for lab monitor displays'),
            (2, 1, 2, 5, 5, 650, 'Keyboard – 5 Nos replacement for damaged terminals'),
            (3, 1, 3, 20, 20, 280, 'A4 Sheets – 20 Reams for record notebooks & lab tests')");

        // Multi-level Approvals
        $pdo->exec("INSERT INTO request_approvals (request_id, level_number, level_title, approver_role, approver_user_id, status, comments, action_date) VALUES
            (1, 1, 'Department Coordinator Verification', 'dept_coordinator', 10, 'Approved', 'Verified technical necessity for semester practical examinations.', '2026-09-20 10:15:00'),
            (1, 2, 'HOD CSE Authorization', 'hod', 9, 'Approved', 'Approved from departmental quota. Forwarded to Store Keeper.', '2026-09-20 14:30:00'),
            (1, 3, 'Central Store Keeper Operational Gate', 'store_keeper', 6, 'Approved', 'Checked existing stock: Zero buffer in central. Exact specs verified. Generated Approval Sheet AS-2026-0001.', '2026-09-21 09:45:00'),
            (1, 4, 'Vice Principal Administrative Sanction', 'vice_principal', 4, 'Pending', 'Awaiting administrative verification (Value ₹12,350)', NULL)");

        // Official Store Keeper Approval Sheet
        $pdo->exec("INSERT INTO approval_sheets (id, sheet_number, request_id, store_keeper_id, existing_stock_notes, spec_verification_notes, vendor_id, quotation_ref, item_price, gst_percent, total_cost, recommendation) VALUES
            (1, 'AS-2026-0001', 1, 6, 'Verified central inventory ledger: Zero surplus stock for items 1 and 2 in store.', 'Inspected specifications: Gold-plated HDMI 2.0 and Dell KB216 conform to AI lab standards.', 1, 'QT-COMP-2026-089', 10466, 18, 12350, 'Recommended for Vice Principal administrative sanction.')");

        // Purchase Orders (Section 16)
        $pdo->exec("INSERT INTO purchase_orders (id, po_number, indent_id, vendor_id, total_amount, tax_amount, terms, status, created_by) VALUES
            (1, 'PO-MSEC-2026-0001', 1, 1, 12350, 1884, '1. Delivery within 10 days of PO receipt.\n2. Payment upon physical inspection and GRN entry.\n3. Standard 1-year on-site replacement warranty.', 'Issued', 7)");

        // Goods Receipts (GRN - Section 6.1 & 16)
        $pdo->exec("INSERT INTO goods_receipts (id, grn_number, po_id, vendor_id, store_id, received_date, verified_by, invoice_no, invoice_date, remarks) VALUES
            (1, 'GRN-2026-00001', 1, 1, 1, '2026-09-22', 6, 'INV-2026-9810', '2026-09-21', 'Factory sealed packaging inspected. 100% sound condition verified.')");

        // Stock Issues & Vouchers (Section 12)
        $pdo->exec("INSERT INTO stock_issues (id, issue_code, request_id, source_store_id, target_dept_id, target_lab_id, issued_by, recipient_id, voucher_type, status, remarks) VALUES
            (1, 'ISS-2026-00001', 1, 1, 1, 2, 6, 10, 'Issue Voucher', 'Issued', 'Materials dispatched for AI Lab semester practical examinations.')");

        $pdo->exec("INSERT INTO stock_issue_items (id, issue_id, item_id, quantity, notes) VALUES
            (1, 1, 1, 10, 'HDMI Cable 2.0 (3M Gold-Plated)'),
            (2, 1, 2, 5, 'Dell KB216 Wired Multimedia Keyboard')");

        // Stock Transactions Ledger
        $pdo->exec("INSERT INTO stock_transactions (id, transaction_type, item_id, store_id, quantity, unit_cost, reference_id, reference_type, notes, created_by) VALUES
            (1, 'INWARD', 1, 1, 50, 350, 1, 'Initial Provision', 'Institutional baseline stock', 6),
            (2, 'INWARD', 2, 1, 40, 650, 1, 'Initial Provision', 'Institutional baseline stock', 6),
            (3, 'INWARD', 3, 1, 100, 280, 1, 'Initial Provision', 'Institutional baseline stock', 6),
            (4, 'OUTWARD', 2, 1, 5, 650, 1, 'Store Issue', 'Dispatched to CSE Department AI Lab', 6),
            (5, 'INWARD', 1, 1, 10, 350, 1, 'GRN Receipt', 'GRN-2026-00001 verified quality stock inward', 6)");

        // Stock Movement (CSE to ECE Transfer)
        $pdo->exec("INSERT INTO stock_movements (id, movement_code, movement_type, source_dept_id, target_dept_id, initiated_by, status, notes) VALUES
            (1, 'MOV-2026-00042', 'DEPT_TO_DEPT', 1, 2, 10, 'In Transit', 'Inter-department transfer: CSE has 10 extra keyboards and ECE requested 5 units')");

        $pdo->exec("INSERT INTO stock_movement_items (id, movement_id, item_id, quantity, remarks) VALUES
            (1, 1, 2, 5, 'Dell USB Keyboards (CSE tech stock to ECE lab)')");

        // Notifications
        $pdo->exec("INSERT INTO notifications (user_id, role_key, type, title, message, link_url, is_read) VALUES
            (4, 'vice_principal', 'APPROVAL_REQUIRED', 'Sanction Required: CSE-REQ-2026-00125', 'Requisition from CSE (₹12,350) store-verified and awaiting administrative approval.', 'index.php?route=view_request&id=1', 0),
            (6, 'store_keeper', 'LOW_STOCK', 'Buffer Alert: A4 Copier Sheets', 'Stock level (10 Reams) is below minimum safety buffer (25 Reams).', 'index.php?route=inventory', 0),
            (9, 'hod', 'INFO', 'Requisition In Progress', 'Requisition CSE-REQ-2026-00125 verified by Store Keeper and sent to Vice Principal.', 'index.php?route=view_request&id=1', 0),
            (1, 'super_admin', 'SECURITY', 'System Operational', 'System initialized with zero security alerts.', 'index.php?route=admin_security', 0)");

        // Permanent Audit Logs
        $pdo->exec("INSERT INTO audit_logs (user_id, role, action, module, entity_type, entity_id, remarks, ip_address) VALUES
            (1, 'super_admin', 'SYSTEM_INITIALIZED', 'System Administration', 'System', 1, 'Initial MSEC SMART STOCK institutional baseline provisioned', '127.0.0.1'),
            (11, 'lab_incharge', 'REQUEST_CREATED', 'Stock Requisition', 'StockRequest', 1, 'Raised indent CSE-REQ-2026-00125 for AI Laboratory Maintenance', '192.168.10.45'),
            (10, 'dept_coordinator', 'COORDINATOR_VERIFIED', 'Approval Workflow', 'StockRequest', 1, 'Coordinator verified lab requirement and technical specifications', '192.168.10.20'),
            (9, 'hod', 'HOD_APPROVED', 'Approval Workflow', 'StockRequest', 1, 'HOD authorized requisition for central store gateway review', '192.168.10.5'),
            (6, 'store_keeper', 'APPROVAL_SHEET_GENERATED', 'Central Store Operations', 'ApprovalSheet', 1, 'Store Keeper completed physical check and issued Approval Sheet AS-2026-0001', '192.168.1.50')");

        // System Settings
        $pdo->exec("INSERT INTO system_settings (setting_key, setting_value, category, description) VALUES
            ('INSTITUTION_NAME', 'Mohamed Sathak Engineering College', 'General', 'Official registered college name'),
            ('INSTITUTION_CODE', 'MSEC-5907', 'General', 'TNEA Counselling Code'),
            ('ACADEMIC_YEAR', '2026-2027', 'Academic', 'Active academic session'),
            ('FINANCIAL_YEAR', '2026-2027', 'Financial', 'Active financial year'),
            ('CURRENCY_CODE', 'INR', 'Financial', 'Indian Rupee symbol ₹'),
            ('TIER_LOW_LIMIT', '5000', 'Workflow', 'Department technical review & preliminary store verification threshold'),
            ('TIER_MID_LIMIT', '25000', 'Workflow', 'Vice Principal administrative review & concurrence threshold'),
            ('TIER_HIGH_LIMIT', '100000', 'Workflow', 'Principal institutional recommendation & Management sanction review'),
            ('MAX_FAILED_LOGINS', '5', 'Security', 'Consecutive failed logins before auto-lock'),
            ('FORGOT_PASSWORD_POLICY', 'ADMIN_ONLY', 'Security', 'Strict institutional policy: Contact System Admin for password reset')");
    }
}

// CLI runner
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    $config = require dirname(__DIR__) . '/config/database.php';
    $driver = $config['default'];
    $conn = $config['connections'][$driver];

    if ($driver === 'sqlite') {
        $pdo = new PDO("sqlite:" . $conn['database'], null, null, $conn['options']);
    } else {
        $dsn = "mysql:host={$conn['host']};port={$conn['port']};dbname={$conn['database']};charset={$conn['charset']}";
        $pdo = new PDO($dsn, $conn['username'], $conn['password'], $conn['options']);
    }

    DatabaseSeeder::run($pdo, $driver);
    echo "MSEC SMART STOCK database successfully seeded via CLI ({$driver}).\n";
}
