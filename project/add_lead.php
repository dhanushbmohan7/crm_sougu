<?php
require("../include/session_chk.php");
include "../include/library.php";

$obj = new Library();
$user_id=$_SESSION['EUSERS_ID'];

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $lead_name = trim($_POST['lead_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $source    = trim($_POST['source'] ?? '');
    $status_id = intval($_POST['status_id'] ?? 1);
    $priority  = intval($_POST['priority'] ?? 3);
    $notes     = trim($_POST['notes'] ?? '');
    $amount    = $_POST['Amount'] ?? '0';

    if (empty($lead_name)) throw new Exception('Lead name is required');
    if (empty($phone))     throw new Exception('Phone number is required');

    // Get max sort_order for this status
    $max_result = $obj->generalquery("SELECT MAX(sort_order) as max_order FROM leads WHERE status_id = $status_id");
    $max_result = mysqli_fetch_assoc($max_result);
    $new_order  = ($max_result['max_order'] ?? 0) + 1;

    // ── AUTO-ASSIGN: pick staff with lowest load ratio today ──────────────
    // Uses created_at (no assigned_at column in leads table)
    $best_staff_id = null;
    $best_ratio    = PHP_INT_MAX;

    // $staff_res = $obj->generalquery("
    //     SELECT
    //         u.USERS_ID,
    //         COALESCE(u.daily_capacity, 50) AS daily_capacity,
    //         COUNT(CASE 
    //             WHEN l.assigned_to = u.USERS_ID
    //              AND DATE(l.created_at) = CURDATE()
    //              AND l.deleted_by_staff = 0
    //             THEN 1 
    //         END) AS today_leads
    //     FROM edu_users u
    //     LEFT JOIN leads l ON l.assigned_to = u.USERS_ID
    //     WHERE u.PRIVILAGE = 'staff'
    //       AND u.UFLAG = '1'
    //     GROUP BY u.USERS_ID, u.daily_capacity
    // ");

    // while ($staff = mysqli_fetch_assoc($staff_res)) {
    //     $cap   = max(1, (int)$staff['daily_capacity']);
    //     $today = (int)$staff['today_leads'];
    //     if ($today >= $cap) continue;       // skip full staff
    //     $ratio = $today / $cap;
    //     if ($ratio < $best_ratio) {
    //         $best_ratio    = $ratio;
    //         $best_staff_id = (int)$staff['USERS_ID'];
    //     }
    // }

    $assigned_val = $user_id;

    // Insert lead
    $sql = "INSERT INTO leads
                (lead_name, email, phone, source, status_id, priority,
                 notes, sort_order, amount, assigned_to)
            VALUES
                ('$lead_name', '$email', '$phone', '$source', $status_id, '$priority',
                 '$notes', $new_order, $amount, $assigned_val)";

    $result = $obj->generalquery($sql);
    if (!$result) throw new Exception('Failed to add lead to database');

    $lead_id = $obj->last_insert_id('leads', 'id');

    // Log assignment
    if ($best_staff_id) {
        $admin_id = (int)($_SESSION['EUSERS_ID'] ?? 0);
        $obj->generalquery("
            INSERT INTO lead_assignment_log (lead_id, from_staff_id, to_staff_id, changed_by, reason)
            VALUES ($lead_id, NULL, $best_staff_id, $admin_id, 'auto_assign')
        ");
    }

    $new_lead = mysqli_fetch_assoc($obj->generalquery("SELECT * FROM leads WHERE id = $lead_id"));

    echo json_encode([
        'status'      => 'success',
        'message'     => 'Lead added successfully',
        'lead'        => $new_lead,
        'assigned_to' => $best_staff_id
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage()
    ]);
}