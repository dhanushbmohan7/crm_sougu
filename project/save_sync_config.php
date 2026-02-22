<?php
require("../include/session_chk.php");
include "../include/library.php";

$obj = new Library();

header('Content-Type: application/json');

try {
    $sheet_url = trim($_POST['sheet_url'] ?? '');
    $default_status = intval($_POST['default_status'] ?? 1);
    $auto_sync = intval($_POST['auto_sync'] ?? 0);
    
    if (empty($sheet_url)) {
        throw new Exception('Sheet URL is required');
    }
    
    // Validate URL
    if (!filter_var($sheet_url, FILTER_VALIDATE_URL)) {
        throw new Exception('Invalid URL format');
    }
    
    // Check if config already exists
    $check_sql = "SELECT id FROM sheet_sync_config ORDER BY id DESC LIMIT 1";
    $check_result = $obj->generalquery($check_sql);
    $check_result = mysqli_fetch_assoc($check_result);
    
    if (!empty($check_result)) {
        // Update existing config
        $sql = "UPDATE sheet_sync_config SET 
                sheet_url = '" . $sheet_url . "',
                default_status = $default_status,
                auto_sync = $auto_sync,
                updated_at = NOW()
                WHERE id = " . $check_result['id'];
    } else {
        // Insert new config
        $sql = "INSERT INTO sheet_sync_config (sheet_url, default_status, auto_sync) 
                VALUES ('" . $sheet_url . "', 
                $default_status, $auto_sync)";
    }
    
    $result = $obj->generalquery($sql);
    
    if ($result) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Sync configuration saved successfully'
        ]);
    } else {
        throw new Exception('Failed to save configuration');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>