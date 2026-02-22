<?php
include "../include/library.php";
$obj = new Library();

header('Content-Type: application/json');

$filter_type = $_POST['filter_type'] ?? '';
$from_date   = $_POST['from_date'] ?? '';
$to_date     = $_POST['to_date'] ?? '';
$user_id     = $_POST['user_id'] ?? 0;
$year        = $_POST['year'] ?? '';
$month       = $_POST['month'] ?? '';


$week = $_POST['week'] ?? '';



$where = " WHERE l.is_deleted = 0 ";

/* ===== DATE FILTER ===== */

if ($filter_type == 'month' && $year && $month) {

    $where .= " AND YEAR(l.created_at) = '$year'
                AND MONTH(l.created_at) = '$month' ";

} elseif ($filter_type == 'year' && $year) {

    $where .= " AND YEAR(l.created_at) = '$year' ";

} elseif ($filter_type == 'custom' && $from_date && $to_date) {

    $from = ($from_date);
    $to   = ($to_date);

    $where .= " AND DATE(l.created_at) BETWEEN '$from' AND '$to' ";
}


if ($filter_type == 'week' && $year && $week) {

    // Get first day of year
    $firstDayOfYear = new DateTime();
    $firstDayOfYear->setISODate($year, $week);

    $weekStart = $firstDayOfYear->format('Y-m-d');

    // Clone and add 6 days
    $weekEndObj = clone $firstDayOfYear;
    $weekEndObj->modify('+6 days');
    $weekEnd = $weekEndObj->format('Y-m-d');

    $where .= " AND DATE(l.created_at) BETWEEN '$weekStart' AND '$weekEnd' ";
}

/* ===== USER FILTER ===== */

if ($user_id != 0) {
    $where .= " AND l.assigned_to = '$user_id' ";
}

/* ================= SUMMARY ================= */

$qSummary = $obj->generalquery("
    SELECT 
        COUNT(*) AS total,
        SUM(l.status_id = 1) AS new_leads,
        SUM(l.status_id = 5) AS won_leads,
        SUM(l.status_id = 6) AS lost_leads,
        SUM(l.status_id = 3) AS followup_leads
    FROM leads l
    $where
");

$summary = mysqli_fetch_assoc($qSummary);

/* ================= MONTH GRAPH ================= */

$months = [];

$qMonth = $obj->generalquery("
    SELECT 
        DATE_FORMAT(l.created_at,'%b %Y') month,
        COUNT(l.id) total
    FROM leads l
    $where
    GROUP BY YEAR(l.created_at), MONTH(l.created_at)
    ORDER BY YEAR(l.created_at), MONTH(l.created_at)
");

while ($row = mysqli_fetch_assoc($qMonth)) {
    $months[] = $row;
}

/* ================= STATUS GRAPH ================= */

$status = [];

$qStatus = $obj->generalquery("
    SELECT 
        lsm.status_name,
        IFNULL(COUNT(l.id),0) total
    FROM lead_status_master lsm
    LEFT JOIN leads l 
        ON l.status_id = lsm.id
        AND l.is_deleted = 0
        AND 1=1
        " . str_replace("WHERE l.is_deleted = 0", "", $where) . "
    GROUP BY lsm.id
");

while ($row = mysqli_fetch_assoc($qStatus)) {
    $status[] = $row;
}

/* ================= USER TABLE ================= */

$users = [];

$qUsers = $obj->generalquery("
    SELECT 
        u.NAME,
        COUNT(l.id) total,
        SUM(l.status_id = 1) AS new_leads,
        SUM(l.status_id = 5) AS won_leads,
        SUM(l.status_id = 6) AS lost_leads,
        SUM(l.status_id = 3) AS followup_leads
    FROM edu_users u
    LEFT JOIN leads l 
        ON l.assigned_to = u.USERS_ID
        AND l.is_deleted = 0
        " . str_replace("WHERE l.is_deleted = 0", "", $where) . "
    WHERE u.UFLAG = 1
    GROUP BY u.USERS_ID
    ORDER BY u.NAME
");

while ($row = mysqli_fetch_assoc($qUsers)) {
    $users[] = $row;
}

echo json_encode([
    'summary' => $summary,
    'months'  => $months,
    'status'  => $status,
    'users'   => $users
]);