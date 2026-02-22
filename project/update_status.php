<?php
require("../include/library.php");
header('Content-Type: application/json');

$lead_id = $_POST['lead_id'] ?? 0;
$status_id = $_POST['status_id'] ?? 0;
$order_list = $_POST['order_list'] ?? '';
$sort_order=0;

if($lead_id && $status_id){
    $obj = new Library();

    // Update lead status
    $sql1 = "UPDATE leads SET status_id='$status_id' WHERE id='$lead_id'";
    $obj->generalquery($sql1);

    // Update sort order for all leads in this column
    if(!empty($order_list)){
        $ids = explode(",", $order_list);
        foreach($ids as $index => $id){
            $id = (int)$id;
            $sort_order = $index + 1;
            $sql2 = "UPDATE leads SET sort_order='$sort_order' WHERE id='$id'";
            $obj->generalquery($sql2);

        }
    }


            $obj->insert('lead_log',array('lead_id'=>$lead_id,'lead_status'=>$status_id,'added_on'=>date('Y:m:d H:i:s'),'sort_order'=>$sort_order,'is_edit'=>0));


    echo json_encode(['status'=>'success']);
} else {
    echo json_encode(['status'=>'error','message'=>'Invalid parameters']);
}
