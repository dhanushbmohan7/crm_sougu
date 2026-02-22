<?php
require("../include/session_chk.php");
include "../include/library.php";

$username  = $_SESSION['EUSERS_NAME'] ?? 'User';
$user_id   = (int)$_SESSION['EUSERS_ID'];
$usergroup = (int)$_SESSION['usergroup'];
$is_admin  = ($usergroup === 1);

$obj = new Library();

// Last sync info
$last_sync_info = null;
$sync_result    = mysqli_fetch_assoc(
    $obj->generalquery("SELECT sheet_url, last_sync_time FROM sheet_sync_config ORDER BY id DESC LIMIT 1")
);
if (!empty($sync_result)) $last_sync_info = $sync_result;

$display = null;
if ($last_sync_info && $last_sync_info['last_sync_time']) {
    $last_sync = strtotime($last_sync_info['last_sync_time']);
    $ago       = time() - $last_sync;
    if      ($ago < 60)    $display = 'Just now';
    elseif  ($ago < 3600)  $display = floor($ago / 60) . ' minutes ago';
    elseif  ($ago < 86400) $display = floor($ago / 3600) . ' hours ago';
    else                   $display = date('M d, H:i', $last_sync);
}

// Staff list for reassign modal (admin only)
// NOTE: uses created_at for today count — no assigned_at column in leads
$staff_list = [];
if ($is_admin) {
    $s_res = $obj->generalquery("
        SELECT 
            u.USERS_ID, u.NAME,
            COALESCE(u.daily_capacity, 50) AS daily_capacity,
            COUNT(CASE 
                WHEN l.assigned_to = u.USERS_ID
                 AND DATE(l.created_at) = CURDATE()
                 AND l.deleted_by_staff = 0
                THEN 1 
            END) AS today_leads
        FROM edu_users u
        LEFT JOIN leads l ON l.assigned_to = u.USERS_ID
        WHERE u.PRIVILAGE = 'staff' AND u.UFLAG = '1'
        GROUP BY u.USERS_ID, u.NAME, u.daily_capacity
        ORDER BY u.NAME ASC
    ");
    while ($r = mysqli_fetch_assoc($s_res)) $staff_list[] = $r;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>CRM - Kanban</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

<style>
body { background: #f5f6fa; }

.page-container {
    margin-left: 250px;
    transition: 0.3s;
    padding: 20px;
}

.kanban-column {
    min-width: 260px;
    background: #ffffff;
    border-radius: 8px;
    padding: 10px;
    margin-right: 15px;
    height: 80vh;
    overflow-y: auto;
    overflow-x: visible;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    min-height: 200px;
}

.kanban-card {
    background: #fff;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 10px;
    cursor: grab;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    position: relative;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.kanban-card:active { cursor: grabbing; }
.kanban-card.dragging {
    transform: rotate(2deg);
    z-index: 9999 !important;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2) !important;
}

/* Soft-deleted cards — admin only sees these */
.kanban-card.deleted-card {
    opacity: 0.6;
    border: 2px dashed #dc3545;
    background: linear-gradient(to right, #ffeaea, #fff8f8) !important;
    cursor: default;
}
.deleted-badge {
    font-size: 10px;
    background: #dc3545;
    color: #fff;
    padding: 1px 5px;
    border-radius: 3px;
    margin-left: 4px;
    vertical-align: middle;
}

.kanban-header {
    font-weight: 600;
    margin-bottom: 15px;
    font-size: 16px;
    padding: 10px;
    border-radius: 6px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.header-content { display: flex; align-items: center; gap: 10px; }
.kanban-header .badge { background: rgba(255,255,255,0.9) !important; color: #333 !important; font-weight: 500; }

.board-container {
    display: flex;
    overflow-x: auto;
    padding-bottom: 20px;
    min-height: 600px;
}

.ui-state-highlight {
    height: 60px;
    background: #d1ecf1;
    border: 2px dashed #0c5460;
    margin-bottom: 10px;
    border-radius: 6px;
}
.ui-sortable-helper { cursor: grabbing !important; }

.star-rating { display: flex; gap: 2px; margin-top: 8px; }
.star { font-size: 14px; cursor: pointer; color: #ddd; transition: color 0.2s; }
.star.active, .star:hover { color: #ffc107; }

.sortable.empty {
    min-height: 100px;
    border: 2px dashed #dee2e6;
    border-radius: 6px;
    margin-top: 10px;
    background: #f8f9fa;
}
.sortable.empty::after {
    content: "Drop leads here";
    display: block;
    text-align: center;
    padding: 40px 20px;
    color: #6c757d;
    font-style: italic;
    font-size: 14px;
}

.kanban-column::-webkit-scrollbar { width: 6px; }
.kanban-column::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 3px; }
.kanban-column::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }
.kanban-column::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }

.add-lead-btn {
    background: transparent; border: none; color: currentColor;
    cursor: pointer; padding: 2px 6px; border-radius: 4px; transition: background 0.2s;
}
.add-lead-btn:hover { background: rgba(255,255,255,0.2); }

.sync-button {
    background: #28a745; color: white; border: none; padding: 8px 16px;
    border-radius: 6px; cursor: pointer; display: flex; align-items: center;
    gap: 8px; font-weight: 500; transition: background 0.2s;
}
.sync-button:hover    { background: #218838; }
.sync-button:disabled { background: #6c757d; cursor: not-allowed; }

.last-sync-info { font-size: 12px; color: #6c757d; margin-left: 10px; }

.lead-details-modal .modal-body { max-height: 70vh; overflow-y: auto; }
.detail-row { margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #f0f0f0; }
.detail-row:last-child { border-bottom: none; }
.detail-label { font-weight: 600; color: #495057; min-width: 120px; }
.detail-value { color: #212529; }

.kanban-card.priority-1 { background: linear-gradient(to right, #ffeaea, #ffffff); }
.kanban-card.priority-2 { background: linear-gradient(to right, #fff3cd, #ffffff); }
.kanban-card.priority-3 { background: linear-gradient(to right, #d1ecf1, #ffffff); }
.kanban-card.priority-4 { background: linear-gradient(to right, #e2e3e5, #ffffff); }
.kanban-card.priority-5 { background: linear-gradient(to right, #f8f9fa, #ffffff); }

.amount-badge {
    background: #28a745 !important; color: white !important;
    padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: 500;
}

/* Card hover action buttons */
.card-actions {
    position: absolute;
    top: 6px; right: 6px;
    display: none;
    gap: 3px;
}
.kanban-card:hover .card-actions { display: flex; }
.card-action-btn {
    background: rgba(255,255,255,0.95);
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 2px 6px;
    font-size: 11px;
    cursor: pointer;
    line-height: 1.5;
}
.card-action-btn.btn-del  { border-color:#dc3545; color:#dc3545; }
.card-action-btn.btn-del:hover  { background:#dc3545; color:#fff; }
.card-action-btn.btn-rst  { border-color:#198754; color:#198754; }
.card-action-btn.btn-rst:hover  { background:#198754; color:#fff; }
.card-action-btn.btn-asgn { border-color:#0d6efd; color:#0d6efd; }
.card-action-btn.btn-asgn:hover { background:#0d6efd; color:#fff; }

/* Staff load bar in reassign modal */
.load-bar  { height: 5px; background: #e9ecef; border-radius: 3px; }
.load-fill { height: 5px; border-radius: 3px; }
</style>
</head>
<body>

<?php include('header.php') ?>

<div class="page-container">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Leads Kanban Board</h2>
            <small class="text-muted">
                <?php if ($is_admin): ?>
                    Admin view — all leads • Drag & drop • Double-click to view details
                <?php else: ?>
                    Your assigned leads • Double-click a lead to view details
                <?php endif; ?>
            </small>
        </div>

        <?php if ($is_admin): ?>
        <div class="d-flex align-items-center gap-3">
            <?php if ($last_sync_info && $last_sync_info['last_sync_time']): ?>
                <div class="last-sync-info">
                    <i class="bi bi-clock-history"></i>
                    Last sync: <?= htmlspecialchars($display) ?>
                </div>
            <?php endif; ?>
            <button class="sync-button" data-bs-toggle="modal" data-bs-target="#syncSheetModal">
                <i class="bi bi-google"></i> Sync Google Sheet
            </button>
        </div>
        <?php endif; ?>
    </div>

    <div class="board-container">
        <?php
        $statuses = $obj->generalquery("SELECT * FROM lead_status_master WHERE is_active=1 ORDER BY sort_order ASC");

        foreach ($statuses as $status):
            $col       = !empty($status['color']) ? $status['color'] : '#ffffff';
            $textColor = '#000';
            if (!empty($status['color']) && $status['color'] !== '#ffffff') {
                $r = hexdec(substr($col,1,2));
                $g = hexdec(substr($col,3,2));
                $b = hexdec(substr($col,5,2));
                $brightness = (($r*299)+($g*587)+($b*114))/1000;
                $textColor  = ($brightness > 128) ? '#000' : '#fff';
            }

            // ── WHERE clause: staff sees only their leads, never deleted ──
            if ($is_admin) {
                $leads_where = "l.status_id = {$status['id']}";
            } else {
                $leads_where = "l.status_id = {$status['id']}
                                AND l.assigned_to = $user_id
                                AND l.deleted_by_staff = 0";
            }

            $cnt = mysqli_fetch_assoc(
                $obj->generalquery("SELECT COUNT(*) AS cnt FROM leads l WHERE $leads_where")
            );
            $lead_count = $cnt['cnt'] ?? 0;

            echo "<div class='kanban-column me-2' data-status-id='{$status['id']}'>";
            echo "<div class='kanban-header' style='background:{$col};color:{$textColor};'>";
            echo "<div class='header-content'><span>{$status['status_name']}</span><span class='badge'>{$lead_count}</span></div>";
            
                echo "<button class='add-lead-btn' data-status-id='{$status['id']}' title='Add Lead'><i class='bi bi-plus-lg'></i></button>";
            
            echo "</div>";

            $leads = $obj->generalquery("
                SELECT l.*, u.NAME AS assigned_staff_name
                FROM leads l
                LEFT JOIN edu_users u ON u.USERS_ID = l.assigned_to
                WHERE $leads_where
                ORDER BY l.sort_order ASC
            ");

            $empty_class = empty($leads) ? 'empty' : '';
            echo "<div class='sortable {$empty_class}' data-status='{$status['id']}'>";

            if (!empty($leads)):
                foreach ($leads as $lead):
                    $lead_name   = htmlspecialchars($lead['lead_name']);
                    $lead_phone  = htmlspecialchars($lead['phone']);
                    $lead_email  = htmlspecialchars($lead['email'] ?? '');
                    $priority    = isset($lead['priority']) ? (int)$lead['priority'] : 3;
                    $amount      = $lead['amount'] ?? null;
                    $is_deleted  = (int)($lead['deleted_by_staff'] ?? 0);
                    $staff_name  = htmlspecialchars($lead['assigned_staff_name'] ?? '');
                    $p_cls       = 'priority-' . $priority;
                    $del_cls     = $is_deleted ? 'deleted-card no-drag' : '';
                    ?>

                    <div class="kanban-card <?= $p_cls ?> <?= $del_cls ?>"
                         data-id="<?= $lead['id'] ?>"
                         data-lead-data="<?= htmlspecialchars(json_encode($lead), ENT_QUOTES, 'UTF-8') ?>">

                        <!-- Hover action buttons -->
                        <div class="card-actions">
                            <?php if ($is_admin && $is_deleted): ?>
                                <button class="card-action-btn btn-rst restore-lead-btn"
                                        data-lead-id="<?= $lead['id'] ?>"
                                        title="Restore lead">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </button>
                            <?php endif; ?>
                            <?php if (!$is_deleted): ?>
                                <?php if ($is_admin): ?>
                                    <button class="card-action-btn btn-asgn reassign-btn"
                                            data-lead-id="<?= $lead['id'] ?>"
                                            data-lead-name="<?= $lead_name ?>"
                                            title="Reassign staff">
                                        <i class="bi bi-person-check"></i>
                                    </button>
                                <?php endif; ?>
                                <button class="card-action-btn btn-del delete-lead-btn"
                                        data-lead-id="<?= $lead['id'] ?>"
                                        data-lead-name="<?= $lead_name ?>"
                                        title="Delete lead">
                                    <i class="bi bi-trash"></i>
                                </button>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex justify-content-between align-items-start">
                            <strong class="flex-grow-1" style="padding-right:60px;">
                                <?= $lead_name ?>
                                <?php if ($is_deleted): ?>
                                    <span class="deleted-badge">DELETED</span>
                                <?php endif; ?>
                            </strong>
                            <div class="d-flex align-items-center gap-1" style="flex-shrink:0;">
                                <?php if (!empty($amount)): ?>
                                    <span class="amount-badge">
                                        <i class="bi bi-currency-rupee"></i><?= number_format($amount, 2) ?>
                                    </span>
                                <?php endif; ?>
                                <small class="text-muted">#<?= $lead['id'] ?></small>
                            </div>
                        </div>

                        <div class="mt-1">
                            <small><i class="bi bi-telephone me-1"></i><?= $lead_phone ?></small>
                            <?php if (!empty($lead_email)): ?>
                                <br><small><i class="bi bi-envelope me-1"></i><?= $lead_email ?></small>
                            <?php endif; ?>
                            <?php if ($is_admin && !empty($staff_name)): ?>
                                <br><small class="text-primary">
                                    <i class="bi bi-person me-1"></i>
                                    <span class="assigned-staff-label"><?= $staff_name ?></span>
                                </small>
                            <?php endif; ?>
                        </div>

                        <?php if (!$is_deleted): ?>
                        <div class="star-rating mt-2">
                            <?php for ($s = 1; $s <= 5; $s++): ?>
                                <span class="star <?= $s <= $priority ? 'active' : '' ?>"
                                      data-value="<?= $s ?>">
                                    <i class="bi bi-star-fill"></i>
                                </span>
                            <?php endfor; ?>
                        </div>
                        <?php endif; ?>

                    </div>

                <?php endforeach;
            endif;
            echo "</div></div>";
        endforeach;
        ?>
    </div>
</div>

<!-- ══════════════════════════════════
     REASSIGN MODAL (admin only)
══════════════════════════════════ -->
<?php if ($is_admin): ?>
<div class="modal fade" id="reassignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-check me-2"></i>Reassign Lead</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3 text-muted small" id="reassignLeadName"></p>
                <input type="hidden" id="reassign_lead_id">

                <label class="form-label fw-semibold mb-2">Select Staff</label>
                <div id="staffCardList">
                    <?php foreach ($staff_list as $st):
                        $cap   = max(1, (int)$st['daily_capacity']);
                        $today = (int)$st['today_leads'];
                        $pct   = min(100, round(($today / $cap) * 100));
                        $bar   = $pct >= 90 ? '#dc3545' : ($pct >= 70 ? '#ffc107' : '#198754');
                    ?>
                    <div class="staff-select-card p-2 mb-2 border rounded"
                         style="cursor:pointer; transition:0.2s;"
                         data-staff-id="<?= $st['USERS_ID'] ?>"
                         data-staff-name="<?= htmlspecialchars($st['NAME']) ?>">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong><?= htmlspecialchars($st['NAME']) ?></strong>
                            <small class="text-muted"><?= $today ?>/<?= $cap ?> today</small>
                        </div>
                        <div class="load-bar mt-1">
                            <div class="load-fill" style="width:<?= $pct ?>%; background:<?= $bar ?>;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-3">
                    <label class="form-label">Reason <small class="text-muted">(optional)</small></label>
                    <input type="text" class="form-control form-control-sm"
                           id="reassign_reason" placeholder="e.g. staff on leave">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="confirmReassignBtn" disabled>
                    <i class="bi bi-check-circle me-1"></i> Confirm
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ══════════════════════════════════
     SYNC SHEET MODAL (admin only)
══════════════════════════════════ -->
<?php if ($is_admin): ?>
<div class="modal fade" id="syncSheetModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-google me-2"></i>Sync Google Sheet</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Google Sheet Published CSV URL</label>
                    <input type="url" class="form-control" id="sheetUrl"
                           placeholder="https://docs.google.com/spreadsheets/d/e/.../pub?output=csv"
                           value="<?= htmlspecialchars($last_sync_info['sheet_url'] ?? '') ?>">
                    <div class="form-text">
                        <small>File → Share → Publish to web → CSV → Copy link</small><br>
                        <small>Columns auto-detected — works with/without headers, any order, any language</small>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Default Status for New Leads</label>
                    <select class="form-select" id="defaultStatus">
                        <?php
                        $statuses_sel = $obj->generalquery("SELECT * FROM lead_status_master WHERE is_active=1 ORDER BY sort_order");
                        foreach ($statuses_sel as $s_row) {
                            $sel = ($s_row['id'] == 1) ? 'selected' : '';
                            echo "<option value='{$s_row['id']}' $sel>{$s_row['status_name']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="autoSync" checked>
                    <label class="form-check-label">Enable auto-sync (every 5 minutes)</label>
                </div>
                <div class="alert alert-info small">
                    <i class="bi bi-info-circle me-1"></i>
                    New leads are auto-assigned to the staff with the most available capacity.
                    Duplicate phone/email rows are skipped automatically.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="startSyncBtn">
                    <i class="bi bi-play-circle me-1"></i> Start Sync
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Sync Progress Modal -->
<div class="modal fade" id="syncProgressModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Syncing Google Sheet</h5></div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="spinner-border text-primary mb-3" style="width:3rem;height:3rem;"></div>
                    <h6 id="syncStatusText">Fetching data from Google Sheet...</h6>
                    <div class="progress mt-3" style="height:20px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated"
                             id="syncProgressBar" style="width:0%">0%</div>
                    </div>
                    <div class="mt-3" id="syncDetails"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sync Results Modal -->
<div class="modal fade" id="syncResultsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sync Results</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="syncResultsContent"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Lead Modal -->
<div class="modal fade" id="addLeadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Lead</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addLeadForm">
                <div class="modal-body">
                    <input type="hidden" name="status_id" id="add_lead_status_id">
                    <div class="mb-3">
                        <label class="form-label">Lead Name *</label>
                        <input type="text" class="form-control" name="lead_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone *</label>
                        <input type="text" class="form-control" name="phone" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Source</label>
                        <select class="form-select" name="source">
                            <option value="">Select Source</option>
                            <option value="Website">Website</option>
                            <option value="Referral">Referral</option>
                            <option value="Social Media">Social Media</option>
                            <option value="Email">Email</option>
                            <option value="Phone Call">Phone Call</option>
                            <option value="Google Sheet">Google Sheet</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number" class="form-control" name="Amount" step="0.01" min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Priority</label>
                        <div class="star-rating mb-2">
                            <?php for ($s = 1; $s <= 5; $s++): ?>
                                <span class="star" data-value="<?= $s ?>"><i class="bi bi-star-fill fs-5"></i></span>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="priority" id="add_priority" value="3">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Lead</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Lead Details Modal -->
<div class="modal fade lead-details-modal" id="leadDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Lead Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="leadDetailsContent"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary edit_btn">Edit</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php include('scripts.php'); ?>
<script type="text/javascript" src="js/crm_scripts.js?v=60"></script>

<script>
// ══════════════════════════════════════════════════════
// DELETE LEAD
// ══════════════════════════════════════════════════════
$(document).on('click', '.delete-lead-btn', function(e) {
    e.stopPropagation();
    const leadId   = $(this).data('lead-id');
    const leadName = $(this).data('lead-name');
    const isAdmin  = <?= $is_admin ? 'true' : 'false' ?>;
    const $card    = $(this).closest('.kanban-card');

    if (isAdmin) {
        if (!confirm('Delete "' + leadName + '"?\n\nOK = Soft delete (you can still see it, staff cannot)\nCancel = Abort')) return;
        const hard   = confirm('Permanently delete? This cannot be undone.\n\nOK = Permanent\nCancel = Soft delete only');
        const action = hard ? 'hard' : 'soft';

        $.post('delete_lead.php', {lead_id: leadId, action: action}, function(res) {
            if (res.status === 'success') {
                if (action === 'hard') {
                    $card.fadeOut(300, function(){ $(this).remove(); });
                } else {
                    $card.addClass('deleted-card no-drag');
                    $card.find('.star-rating').remove();
                    $card.find('.delete-lead-btn, .reassign-btn').hide();
                    $card.find('strong').first().append('<span class="deleted-badge">DELETED</span>');
                    $card.find('.card-actions').prepend(
                        '<button class="card-action-btn btn-rst restore-lead-btn" data-lead-id="'+leadId+'" title="Restore"><i class="bi bi-arrow-counterclockwise"></i></button>'
                    );
                }
            } else { alert(res.message); }
        }, 'json');

    } else {
        if (!confirm('Delete "' + leadName + '"?\nYou will not see this lead anymore.')) return;
        $.post('delete_lead.php', {lead_id: leadId, action: 'soft'}, function(res) {
            if (res.status === 'success') {
                $card.fadeOut(300, function(){ $(this).remove(); });
            } else { alert(res.message); }
        }, 'json');
    }
});

// ══════════════════════════════════════════════════════
// RESTORE LEAD (admin only)
// ══════════════════════════════════════════════════════
$(document).on('click', '.restore-lead-btn', function(e) {
    e.stopPropagation();
    if (!confirm('Restore this lead?')) return;
    const leadId = $(this).data('lead-id');

    $.post('delete_lead.php', {lead_id: leadId, action: 'restore'}, function(res) {
        if (res.status === 'success') { location.reload(); }
        else { alert(res.message); }
    }, 'json');
});

// ══════════════════════════════════════════════════════
// REASSIGN (admin only)
// ══════════════════════════════════════════════════════
let selectedStaffId = null;

$(document).on('click', '.reassign-btn', function(e) {
    e.stopPropagation();
    $('#reassign_lead_id').val($(this).data('lead-id'));
    $('#reassignLeadName').text('Lead: ' + $(this).data('lead-name'));
    selectedStaffId = null;
    $('#confirmReassignBtn').prop('disabled', true);
    $('.staff-select-card').removeClass('border-primary bg-light');
    $('#reassign_reason').val('');
    new bootstrap.Modal(document.getElementById('reassignModal')).show();
});

$(document).on('click', '.staff-select-card', function() {
    $('.staff-select-card').removeClass('border-primary bg-light');
    $(this).addClass('border-primary bg-light');
    selectedStaffId = $(this).data('staff-id');
    $('#confirmReassignBtn').prop('disabled', false);
});

$('#confirmReassignBtn').on('click', function() {
    const leadId     = $('#reassign_lead_id').val();
    const staffName  = $('.staff-select-card.border-primary').data('staff-name');
    const reason     = $('#reassign_reason').val() || 'manual_reassign';

    $.post('assign_lead.php', {
        lead_id:     leadId,
        to_staff_id: selectedStaffId,
        reason:      reason
    }, function(res) {
        if (res.status === 'success') {
            bootstrap.Modal.getInstance(document.getElementById('reassignModal')).hide();
            // Update staff name label on the card
            const $card = $('.kanban-card[data-id="' + leadId + '"]');
            $card.find('.assigned-staff-label').text(res.staff_name);
            $card.css('outline', '2px solid #0d6efd');
            setTimeout(() => $card.css('outline',''), 800);
        } else { alert(res.message); }
    }, 'json');
});
</script>

</body>
</html>