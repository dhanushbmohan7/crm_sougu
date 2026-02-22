<?php
require("../include/session_chk.php");
include "../include/library.php";

$obj = new Library();

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $user_id=$_SESSION['EUSERS_ID'];;

    $lead_id = intval($_POST['lead_id'] ?? 0);
    $lead_name = trim($_POST['lead_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $source = trim($_POST['source'] ?? '');
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : null;
    $priority = intval($_POST['priority'] ?? 3);
    $notes = trim($_POST['notes'] ?? '');


    $requirements = trim($_POST['requirements'] ?? '');
$location = trim($_POST['location'] ?? '');
$next_follow_up = !empty($_POST['next_follow_up']) ? $_POST['next_follow_up'] : null;
    
    if ($lead_id <= 0) {
        throw new Exception('Invalid lead ID');
    }
    
    if (empty($lead_name)) {
        throw new Exception('Lead name is required');
    }
    
  
    
    // Validate priority range
    if ($priority < 1 || $priority > 5) {
        throw new Exception('Priority must be between 1 and 5');
    }
    
   
    
    // Build update query in your preferred format
    // $sql = "UPDATE leads SET 
    //         lead_name = '$lead_name', 
    //         email = '$email', 
    //         phone = '$phone', 
    //         source = '$source', 
    //         amount = $amount, 
    //         priority = $priority, 
    //         notes = '$notes',
    //         updated_at = NOW()
    //         WHERE id = $lead_id";

    $sql = "UPDATE leads SET 
        lead_name = '$lead_name', 
        email = '$email', 
        
        location = '$location', 
        amount = " . ($amount !== null ? $amount : 'NULL') . ", 
        priority = $priority, 
        notes = '$notes',
        requiremnts = '$requirements', 
        next_follow_up = " . ($next_follow_up ? "'$next_follow_up'" : 'NULL') . ",
        updated_at = NOW()
        WHERE id = $lead_id";
    
    $result = $obj->generalquery($sql);
    
    if ($result) {






        
        // Get updated lead data
        $sql_lead = "SELECT * FROM leads WHERE id = $lead_id";
        $updated_lead = $obj->generalquery($sql_lead);
        $updated_lead = mysqli_fetch_assoc($updated_lead);
        
$obj->insert('lead_log',array('lead_id'=>$lead_id,'lead_status'=>$updated_lead['status_id'],'added_on'=>date('Y:m:d H:i:s'),'notes'=>$notes,'requirements'=> $requirements,'next_follow_up'=>$next_follow_up,'amount'=>$amount,'added_by'=>$user_id,'sort_order'=>$updated_lead['sort_order'],'is_edit'=>1));

        echo json_encode([
            'status' => 'success',
            'message' => 'Lead updated successfully',
            'lead' => $updated_lead[0] ?? []
        ]);
    } else {
        throw new Exception('Failed to update lead');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}