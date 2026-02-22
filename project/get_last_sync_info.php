<?php
require("../include/session_chk.php");
include "../include/library.php";

$obj = new Library();

$sql = "SELECT last_sync_time FROM sheet_sync_config ORDER BY id DESC LIMIT 1";

$result = $obj->generalquery($sql);


$result=mysqli_fetch_all($result,MYSQLI_ASSOC);

if (!empty($result) && $result[0]['last_sync_time']) {
    $last_sync = strtotime($result[0]['last_sync_time']);
    $time_ago = time() - $last_sync;
    
    if ($time_ago < 60) {
        $display = 'Just now';
    } elseif ($time_ago < 3600) {
        $display = floor($time_ago / 60) . ' minutes ago';
    } elseif ($time_ago < 86400) {
        $display = floor($time_ago / 3600) . ' hours ago';
    } else {
        $display = date('M d, H:i', $last_sync);
    }
    
    echo '<i class="bi bi-clock-history"></i> Last sync: ' . $display;
} else {
    echo '<i class="bi bi-clock-history"></i> Never synced';
}
?>