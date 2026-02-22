<?php
require("../include/session_chk.php");
include "../include/library.php";

$obj      = new Library();
$admin_id = (int)$_SESSION['EUSERS_ID'];

header('Content-Type: application/json');

try {
    if ((int)$_SESSION['usergroup'] !== 1) {
        throw new Exception('Only admin can reassign leads');
    }

    $lead_id     = intval($_POST['lead_id']     ?? 0);
    $to_staff_id = intval($_POST['to_staff_id'] ?? 0);
    $reason      = trim($_POST['reason']        ?? 'manual_reassign');

    if (!$lead_id)     throw new Exception('Invalid lead');
    if (!$to_staff_id) throw new Exception('Please select a staff member');

    // Get current assignment for audit log
    $cur       = mysqli_fetch_assoc($obj->generalquery("SELECT assigned_to FROM leads WHERE id = $lead_id"));
    $from_sql  = ($cur && $cur['assigned_to']) ? (int)$cur['assigned_to'] : 'NULL';

    // Update lead
    $obj->generalquery("UPDATE leads SET assigned_to = $to_staff_id WHERE id = $lead_id");

    // Audit log
    $reason_esc = addslashes($reason);
    $obj->generalquery("
        INSERT INTO lead_assignment_log (lead_id, from_staff_id, to_staff_id, changed_by, reason)
        VALUES ($lead_id, $from_sql, $to_staff_id, $admin_id, '$reason_esc')
    ");

    $staff = mysqli_fetch_assoc($obj->generalquery("SELECT NAME FROM edu_users WHERE USERS_ID = $to_staff_id"));

    echo json_encode([
        'status'     => 'success',
        'message'    => 'Lead reassigned to ' . ($staff['NAME'] ?? ''),
        'staff_name' => $staff['NAME'] ?? ''
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}