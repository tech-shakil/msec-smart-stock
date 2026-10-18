<?php
/**
 * MSEC SMART STOCK - Dynamic Role-Specific Dashboard Router
 * Implements Section 3 & 28: Each of the 14 roles receives its own dedicated dashboard workspace.
 */

$user = Auth::user();
$role = $user['role_key'] ?? 'super_admin';

// Map role to its dedicated dashboard view
$dashboardFile = match ($role) {
    'super_admin', 'admin' => __DIR__ . '/../dashboards/admin.php',
    'director'            => __DIR__ . '/../dashboards/director.php',
    'principal'           => __DIR__ . '/../dashboards/principal.php',
    'vice_principal'      => __DIR__ . '/../dashboards/vice_principal.php',
    'management_viewer'   => __DIR__ . '/../dashboards/viewer.php',
    'store_keeper'        => __DIR__ . '/../dashboards/store_keeper.php',
    'purchase_officer'    => __DIR__ . '/../dashboards/purchase_officer.php',
    'accounts_officer'    => __DIR__ . '/../dashboards/accounts_officer.php',
    'hod'                 => __DIR__ . '/../dashboards/hod.php',
    'dept_coordinator'    => __DIR__ . '/../dashboards/coordinator.php',
    'lab_incharge'        => __DIR__ . '/../dashboards/lab_incharge.php',
    'technician'          => __DIR__ . '/../dashboards/technician.php',
    'auditor'             => __DIR__ . '/../dashboards/auditor.php',
    default               => __DIR__ . '/../dashboards/store_keeper.php'
};

if (file_exists($dashboardFile)) {
    include $dashboardFile;
} else {
    include __DIR__ . '/../dashboards/store_keeper.php';
}
