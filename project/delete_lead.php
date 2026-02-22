<?php
require("../include/session_chk.php");
include "../include/library.php";

$obj       = new Library();
$user_id   = (int)$_SESSION['EUSERS_ID'];
$usergroup = (int)$_SESSION['usergroup'];   // 1 = admin, 2 = staff

header('Content-Type: application/json');

try {
    $lead_id = intval($_POST['lead_id'] ?? 0);
    $action  = trim($_POST['action']   ?? 'soft');   // soft | hard | restore

    if (!$lead_id) throw new Exception('Invalid lead');

    if ($action === 'soft') {
        $where = $usergroup === 1
            ? "id = $lead_id"
            : "id = $lead_id AND assigned_to = $user_id";

        $obj->generalquery("
            UPDATE leads
            SET deleted_by_staff    = 1,
                deleted_by_staff_id = $user_id,
                deleted_by_staff_at = NOW()
            WHERE $where AND deleted_by_staff = 0
        ");
        echo json_encode(['status' => 'success', 'message' => 'Lead deleted']);

    } elseif ($action === 'restore') {
        if ($usergroup !== 1) throw new Exception('Only admin can restore leads');
        $obj->generalquery("
            UPDATE leads
            SET deleted_by_staff    = 0,
                deleted_by_staff_id = NULL,
                deleted_by_staff_at = NULL
            WHERE id = $lead_id
        ");
        echo json_encode(['status' => 'success', 'message' => 'Lead restored']);

    } elseif ($action === 'hard') {
        if ($usergroup !== 1) throw new Exception('Only admin can permanently delete leads');
        $obj->generalquery("DELETE FROM lead_assignment_log WHERE lead_id = $lead_id");
        $obj->generalquery("DELETE FROM leads WHERE id = $lead_id");
        echo json_encode(['status' => 'success', 'message' => 'Lead permanently deleted']);

    } else {
        throw new Exception('Unknown action');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}