<?php
require("../include/session_chk.php");
include "../include/library.php";

$obj = new Library();

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $lead_id = intval($_POST['lead_id'] ?? 0);
    $priority = intval($_POST['priority'] ?? 3);
    
    if ($lead_id <= 0) {
        throw new Exception('Invalid lead ID');
    }
    
    // Validate priority range
    if ($priority < 1 || $priority > 5) {
        throw new Exception('Priority must be between 1 and 5');
    }
    
    $sql = "UPDATE leads SET priority = $priority WHERE id = $lead_id";
    
    
    $result = $obj->generalquery($sql);
    
    if ($result) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Priority updated successfully'
        ]);
    } else {
        throw new Exception('Failed to update priority');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}