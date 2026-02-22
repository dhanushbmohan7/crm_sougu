<?php
require("../include/session_chk.php");
include "../include/library.php";

$obj = new Library();
header('Content-Type: application/json');

// ═══════════════════════════════════════════════════════════
// SMART COLUMN DETECTOR
// ═══════════════════════════════════════════════════════════

function rowLooksLikeHeader(array $row): bool {
    $keywords = [
        'name','email','phone','mobile','address','source','notes','note',
        'company','amount','budget','remark','comment','contact',
        'mob','ph','mail','nm','em','tel','amt','src','lead',
        'staff','assigned','executive','agent','owner','advisor'
    ];
    $matches = 0;
    $total   = 0;
    foreach ($row as $cell) {
        $cell = strtolower(trim($cell));
        if ($cell === '') continue;
        $total++;
        if (preg_match('/^\+?[\d\s\-()]{7,}$/', $cell)) continue;
        if (filter_var($cell, FILTER_VALIDATE_EMAIL)) continue;
        foreach ($keywords as $kw) {
            if (strpos($cell, $kw) !== false) { $matches++; break; }
        }
    }
    if ($total === 0) return false;
    return ($matches / $total) >= 0.25;
}

function mapByHeader(array $headers): array {
    $synonyms = [
        'lead_name' => ['name','lead name','full name','fullname','contact name','contact',
                        'customer','client','person','lead','nm','nombre'],
        'email'     => ['email','e-mail','mail','email address','email id','emailid'],
        'phone'     => ['phone','mobile','cell','telephone','tel','ph','ph no','mob',
                        'mob no','whatsapp','contact no','number','phone number','mobile no'],
        'notes'     => ['notes','note','remark','remarks','comment','comments','description'],
        'amount'    => ['amount','value','budget','amt','deal value','revenue','price'],
        'source'    => ['source','lead source','from','channel','medium','campaign','referral'],
        // NEW STAFF COLUMN SUPPORT
        'staff_name'=> ['staff','staff name','assigned','assigned to','assigned person',
                        'executive','sales person','sales executive','handled by',
                        'agent','owner','advisor','counsellor','counselor']
    ];

    $mapping = [];
    foreach ($headers as $idx => $raw) {
        $h = strtolower(trim($raw));
        if ($h === '') continue;
        foreach ($synonyms as $field => $words) {
            if (isset($mapping[$field])) continue;
            foreach ($words as $w) {
                if ($h === $w || strpos($h, $w) !== false) {
                    $mapping[$field] = $idx;
                    break;
                }
            }
        }
    }
    return $mapping;
}

function mapByPattern(array $rows): array {
    $num_cols = 0;
    foreach ($rows as $r) $num_cols = max($num_cols, count($r));

    $scores = [];
    foreach (array_slice($rows, 0, 15) as $row) {
        foreach ($row as $col => $val) {
            $val = trim($val);
            if ($val === '') continue;

            if (filter_var($val, FILTER_VALIDATE_EMAIL)) {
                $scores[$col]['email'] = ($scores[$col]['email'] ?? 0) + 3;
            }

            if (preg_match('/^\+?[\d\s\-()]{7,15}$/', $val)) {
                $scores[$col]['phone'] = ($scores[$col]['phone'] ?? 0) + 3;
            }

            if (is_numeric(str_replace([',','.'], '', $val)) && strlen($val) <= 12 && strlen($val) >= 4) {
                $digits = preg_replace('/\D/', '', $val);
                if (strlen($digits) >= 7) {
                    $scores[$col]['phone']  = ($scores[$col]['phone']  ?? 0) + 1;
                } else {
                    $scores[$col]['amount'] = ($scores[$col]['amount'] ?? 0) + 2;
                }
            }

            if (strlen($val) > 60) {
                $scores[$col]['notes'] = ($scores[$col]['notes'] ?? 0) + 2;
            }

            if (preg_match('/^[a-zA-Z\s\.\-\']{3,40}$/', $val) && !filter_var($val, FILTER_VALIDATE_EMAIL)) {
                $scores[$col]['lead_name'] = ($scores[$col]['lead_name'] ?? 0) + 1;
                $scores[$col]['staff_name'] = ($scores[$col]['staff_name'] ?? 0) + 1;
            }
        }
    }

    $mapping       = [];
    $assigned_cols = [];
    $priority_fields = ['email','phone','amount','notes','lead_name','staff_name','source'];

    foreach ($priority_fields as $field) {
        $best_col = null; $best_score = 0;
        foreach ($scores as $col => $field_scores) {
            if (in_array($col, $assigned_cols)) continue;
            $score = $field_scores[$field] ?? 0;
            if ($score > $best_score) { $best_score = $score; $best_col = $col; }
        }
        if ($best_col !== null && $best_score > 0) {
            $mapping[$field] = $best_col;
            $assigned_cols[] = $best_col;
        }
    }

    if (!isset($mapping['lead_name'])) {
        for ($c = 0; $c < $num_cols; $c++) {
            if (!in_array($c, $assigned_cols)) {
                $mapping['lead_name'] = $c;
                break;
            }
        }
    }
    return $mapping;
}

// ── Pick staff with lowest load ratio today ───────────────────────────────
function pickBestStaff($obj): ?int {
    $res = $obj->generalquery("
        SELECT
            u.USERS_ID,
            COALESCE(u.daily_capacity, 50) AS daily_capacity,
            COUNT(CASE 
                WHEN l.assigned_to = u.USERS_ID
                 AND DATE(l.created_at) = CURDATE()
                 AND l.deleted_by_staff = 0
                THEN 1 
            END) AS today_leads
        FROM edu_users u
        LEFT JOIN leads l ON l.assigned_to = u.USERS_ID
        WHERE u.PRIVILAGE = 'staff'
          AND u.UFLAG = '1'
        GROUP BY u.USERS_ID, u.daily_capacity
    ");

    $best_id    = null;
    $best_ratio = PHP_INT_MAX;

    while ($row = mysqli_fetch_assoc($res)) {
        $cap   = max(1, (int)$row['daily_capacity']);
        $today = (int)$row['today_leads'];
        if ($today >= $cap) continue;
        $ratio = $today / $cap;
        if ($ratio < $best_ratio) {
            $best_ratio = $ratio;
            $best_id    = (int)$row['USERS_ID'];
        }
    }
    return $best_id;
}

// NEW: Match staff by name
function findStaffByName($obj, $name): ?int {
    $name = trim($name);
    if ($name === '') return null;

    $name_esc = addslashes($name);

    $res = $obj->generalquery("
        SELECT USERS_ID 
        FROM edu_users
        WHERE PRIVILAGE = 'staff'
          AND UFLAG = '1'
          AND (
                LOWER(NAME) = LOWER('$name_esc')
             OR LOWER(NAME) LIKE LOWER('%$name_esc%')
          )
        LIMIT 1
    ");

    $row = mysqli_fetch_assoc($res);
    return $row ? (int)$row['USERS_ID'] : null;
}

// ═══════════════════════════════════════════════════════════
// MAIN SYNC LOGIC
// ═══════════════════════════════════════════════════════════
try {

    $sheet_url      = trim($_POST['sheet_url'] ?? '');
    $default_status = intval($_POST['default_status'] ?? 1);
    $auto_sync      = isset($_POST['auto_sync']) ? intval($_POST['auto_sync']) : 0;
    $admin_id       = (int)($_SESSION['EUSERS_ID'] ?? 0);

    if (empty($sheet_url)) throw new Exception('Sheet URL is required');

    $csv_data = @file_get_contents($sheet_url);
    if ($csv_data === false) {
        throw new Exception('Failed to fetch data from Google Sheet. Check the URL and permissions.');
    }

    $lines = array_values(array_filter(
        array_map('rtrim', explode("\n", $csv_data)),
        fn($l) => trim($l) !== ''
    ));

    if (empty($lines)) throw new Exception('No data found in Google Sheet');

    $all_rows = [];
    foreach ($lines as $line) {
        $all_rows[] = str_getcsv($line);
    }

    $has_header = rowLooksLikeHeader($all_rows[0]);

    if ($has_header) {
        $mapping   = mapByHeader($all_rows[0]);
        $data_rows = array_slice($all_rows, 1);
    } else {
        $mapping   = mapByPattern($all_rows);
        $data_rows = $all_rows;
    }

    if (!isset($mapping['lead_name'])) $mapping['lead_name'] = 0;

    $new_leads  = 0;
    $duplicates = 0;
    $errors     = 0;
    $row_index  = 0;

    foreach ($data_rows as $row) {

        $row_index++;
        if (count(array_filter($row, fn($v) => trim($v) !== '')) === 0) continue;

        $lead_name = isset($mapping['lead_name'], $row[$mapping['lead_name']]) ? trim($row[$mapping['lead_name']]) : '';
        $email     = isset($mapping['email'],     $row[$mapping['email']])     ? trim($row[$mapping['email']])     : '';
        $phone     = isset($mapping['phone'],     $row[$mapping['phone']])     ? trim($row[$mapping['phone']])     : '';
        $notes     = isset($mapping['notes'],     $row[$mapping['notes']])     ? trim($row[$mapping['notes']])     : '';
        $source    = isset($mapping['source'],    $row[$mapping['source']])    ? trim($row[$mapping['source']])    : 'Google Sheet';
        $staff_name= isset($mapping['staff_name'],$row[$mapping['staff_name']])? trim($row[$mapping['staff_name']]): '';
        $raw_amt   = isset($mapping['amount'],    $row[$mapping['amount']])    ? trim($row[$mapping['amount']])    : '';
        $amount    = ($raw_amt !== '') ? floatval(preg_replace('/[^\d\.]/', '', $raw_amt)) : 0;

        if (empty($source)) $source = 'Google Sheet';
        if (empty($lead_name) && empty($phone) && empty($email)) continue;

        $row_hash     = md5($phone);
        $row_hash_esc = addslashes($row_hash);

        $hash_check = mysqli_fetch_all(
            $obj->generalquery("SELECT id FROM leads WHERE sheet_row_hash = '$row_hash_esc' LIMIT 1"),
            MYSQLI_ASSOC
        );
        if (!empty($hash_check)) { $duplicates++; continue; }

        $skip = false;

        if (!empty($email)) {
            $e_esc = addslashes($email);
            $e_chk = mysqli_fetch_all(
                $obj->generalquery("SELECT id FROM leads WHERE email = '$e_esc' LIMIT 1"),
                MYSQLI_ASSOC
            );
            if (!empty($e_chk)) { $duplicates++; $skip = true; }
        }

        if (!$skip && !empty($phone)) {
            $p_esc = addslashes($phone);
            $p_chk = mysqli_fetch_all(
                $obj->generalquery("SELECT id FROM leads WHERE phone = '$p_esc' LIMIT 1"),
                MYSQLI_ASSOC
            );
            if (!empty($p_chk)) { $duplicates++; $skip = true; }
        }

        if ($skip) continue;

        // 🔥 NEW ASSIGNMENT LOGIC
        $assigned_staff_id = null;

        if (!empty($staff_name)) {
            $matched_staff = findStaffByName($obj, $staff_name);
            if ($matched_staff) {
                $assigned_staff_id = $matched_staff;
            }
        }

        if (!$assigned_staff_id) {
            $assigned_staff_id = pickBestStaff($obj);
        }

        $assigned_val = $assigned_staff_id ? $assigned_staff_id : 'NULL';

        $order_res  = mysqli_fetch_all(
            $obj->generalquery("SELECT COALESCE(MAX(sort_order),0)+1 AS no FROM leads WHERE status_id=$default_status"),
            MYSQLI_ASSOC
        );
        $next_order = $order_res[0]['no'] ?? 1;

        $ln_e  = addslashes($lead_name);
        $em_e  = addslashes($email);
        $ph_e  = addslashes($phone);
        $src_e = addslashes($source);
        $nt_e  = addslashes($notes);

        $sql = "INSERT INTO leads
                    (lead_name, email, phone, source, amount, priority, notes,
                     status_id, sort_order, sheet_row_hash, assigned_to,
                     created_at, updated_at)
                VALUES
                    ('$ln_e','$em_e','$ph_e','$src_e',$amount,3,'$nt_e',
                     $default_status,$next_order,'$row_hash_esc',$assigned_val,
                     NOW(),NOW())";

        $result = $obj->generalquery($sql);

        if ($result) {
            $new_lead_id = $obj->last_insert_id('leads', 'id');
            $new_leads++;

            if ($assigned_staff_id) {
                $obj->generalquery("
                    INSERT INTO lead_assignment_log
                        (lead_id, from_staff_id, to_staff_id, changed_by, reason)
                    VALUES
                        ($new_lead_id, NULL, $assigned_staff_id, $admin_id, 'auto_assign_sheet')
                ");
            }
        } else {
            $errors++;
        }
    }

    $url_esc = addslashes($sheet_url);
    $obj->generalquery("UPDATE sheet_sync_config SET last_sync_time=NOW() WHERE sheet_url='$url_esc'");

    echo json_encode([
        'status'     => 'success',
        'message'    => 'Sync completed successfully',
        'new_leads'  => $new_leads,
        'duplicates' => $duplicates,
        'errors'     => $errors,
        'total_rows' => $row_index,
        'has_header' => $has_header,
        'mapping'    => $mapping,
        'sync_time'  => date('Y-m-d H:i:s'),
    ]);

} catch (Exception $e) {
    error_log('Sync error: ' . $e->getMessage());
    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage()
    ]);
}
