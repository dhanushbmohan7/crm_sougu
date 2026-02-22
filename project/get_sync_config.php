<?php
require("../include/session_chk.php");
include "../include/library.php";
$obj = new Library();
header('Content-Type: application/json');
$sql = "SELECT sheet_url, default_status, auto_sync FROM sheet_sync_config ORDER BY id DESC LIMIT 1";
$result = $obj->generalquery($sql);
$row = mysqli_fetch_assoc($result);
if ($row) {
    echo json_encode([
        'status' => 'success',
        'config' => [
            'sheet_url' => $row['sheet_url'],
            'default_status' => intval($row['default_status']),
            'auto_sync' => intval($row['auto_sync'])
        ]
    ]);
} else {
    echo json_encode([
        'status' => 'success',
        'config' => null
    ]);
}
?>