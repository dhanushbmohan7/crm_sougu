<?php
require("../include/session_chk.php");
include "../include/library.php";

$username = $_SESSION['username'] ?? 'User';
$obj = new Library();

// Handle CREATE
if (isset($_POST['create'])) {
    $name         = $_POST['name'];
    $username_val = $_POST['username'];
    $password     = $_POST['password'];
    $usergroup    = $_POST['usergroup'];
    $daily_cap    = max(1, intval($_POST['daily_capacity'] ?? 50));

    $sql = "INSERT INTO edu_users (NAME, USERNAME, PASSWORD, usergroup, PRIVILAGE, daily_capacity)
            VALUES ('$name', '$username_val', '$password', '$usergroup', 'staff', $daily_cap)";
    $obj->generalquery($sql);
    header("Location: staff_master.php?msg=created");
    exit;
}

// Handle UPDATE
if (isset($_POST['update'])) {
    $id           = $_POST['id'];
    $name         = $_POST['name'];
    $username_val = $_POST['username'];
    $password     = $_POST['password'];
    $usergroup    = $_POST['usergroup'];
    $daily_cap    = max(1, intval($_POST['daily_capacity'] ?? 50));

    if (!empty($password)) {
        $sql = "UPDATE edu_users 
                SET NAME='$name', USERNAME='$username_val', PASSWORD='$password',
                    usergroup='$usergroup', daily_capacity=$daily_cap
                WHERE USERS_ID=$id";
    } else {
        $sql = "UPDATE edu_users 
                SET NAME='$name', USERNAME='$username_val',
                    usergroup='$usergroup', daily_capacity=$daily_cap
                WHERE USERS_ID=$id";
    }
    $obj->generalquery($sql);
    header("Location: staff_master.php?msg=updated");
    exit;
}

// Handle DELETE
if (isset($_GET['delete'])) {
    $id  = $_GET['delete'];
    $sql = "DELETE FROM edu_users WHERE USERS_ID=$id";
    $obj->generalquery($sql);
    header("Location: staff_master.php?msg=deleted");
    exit;
}

// Fetch all staff with today's assigned lead count
// NOTE: leads table has no assigned_at column — using created_at for "today" count
$data = $obj->generalquery("
    SELECT 
        u.USERS_ID, u.NAME, u.USERNAME, u.usergroup,
        COALESCE(u.daily_capacity, 50) AS daily_capacity,
        COUNT(CASE 
            WHEN l.assigned_to = u.USERS_ID
             AND DATE(l.created_at) = CURDATE()
             AND l.deleted_by_staff = 0
            THEN 1 
        END) AS today_leads
    FROM edu_users u
    LEFT JOIN leads l ON l.assigned_to = u.USERS_ID
    GROUP BY u.USERS_ID, u.NAME, u.USERNAME, u.usergroup, u.daily_capacity
    ORDER BY u.USERS_ID ASC
");
$data = mysqli_fetch_all($data, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>CRM - Staff Master</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<style>
body { background: #f5f6fa; }
.page-container { margin-left: 250px; transition: 0.3s; padding: 20px; }
.load-bar  { height: 6px; border-radius: 3px; background: #e9ecef; }
.load-fill { height: 6px; border-radius: 3px; transition: width 0.3s; }
</style>
</head>
<body>

<?php include('header.php') ?>

<div class="page-container">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Staff Master</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
            <i class="bi bi-plus-circle"></i> Add Staff
        </button>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            Action Completed Successfully
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <table class="table table-bordered table-striped align-middle">
        <thead>
            <tr class="table-dark">
                <th>#</th>
                <th>Name</th>
                <th>Username</th>
                <th>User Group</th>
                <th>Daily Capacity</th>
                <th>Today's Load</th>
                <th width="140">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($data)): ?>
                <?php foreach ($data as $row):
                    $usergroup_text  = ($row['usergroup'] == 1) ? 'Admin' : 'Staff';
                    $usergroup_badge = ($row['usergroup'] == 1) ? 'danger' : 'primary';
                    $cap             = max(1, (int)$row['daily_capacity']);
                    $today           = (int)$row['today_leads'];
                    $pct             = min(100, round(($today / $cap) * 100));
                    $bar_color       = $pct >= 90 ? '#dc3545' : ($pct >= 70 ? '#ffc107' : '#198754');
                ?>
                <tr>
                    <td><?= $row['USERS_ID'] ?></td>
                    <td><?= htmlspecialchars($row['NAME']) ?></td>
                    <td><?= htmlspecialchars($row['USERNAME']) ?></td>
                    <td><span class="badge bg-<?= $usergroup_badge ?>"><?= $usergroup_text ?></span></td>
                    <td><?= $cap ?> / day</td>
                    <td style="min-width:140px;">
                        <div class="d-flex justify-content-between mb-1" style="font-size:12px;">
                            <span><?= $today ?> today</span>
                            <span>
                                <?php if (($cap - $today) > 0): ?>
                                    <?= $cap - $today ?> free
                                <?php else: ?>
                                    <span class="text-danger fw-bold">Full</span>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="load-bar">
                            <div class="load-fill" style="width:<?= $pct ?>%; background:<?= $bar_color ?>;"></div>
                        </div>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-warning editBtn"
                            data-id="<?= $row['USERS_ID'] ?>"
                            data-name="<?= htmlspecialchars($row['NAME']) ?>"
                            data-username="<?= htmlspecialchars($row['USERNAME']) ?>"
                            data-usergroup="<?= $row['usergroup'] ?>"
                            data-capacity="<?= $cap ?>"
                            data-bs-toggle="modal"
                            data-bs-target="#editModal">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <a href="?delete=<?= $row['USERS_ID'] ?>"
                            onclick="return confirm('Delete this staff member?')"
                            class="btn btn-sm btn-danger">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" class="text-center">No staff members found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

</div>

<!-- Create Modal -->
<div class="modal fade" id="createModal">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5>Add Staff Member</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php include "staff_form.php"; ?>
                <div class="mb-3">
                    <label class="form-label">Daily Lead Capacity <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" name="daily_capacity"
                           id="daily_capacity" min="1" max="999" value="50" required>
                    <div class="form-text">Max leads this staff can handle per day.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" name="create" class="btn btn-primary">Save Staff</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header bg-warning">
                <h5>Edit Staff Member</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="edit_id">
                <?php include "staff_form.php"; ?>
                <div class="mb-3">
                    <label class="form-label">Daily Lead Capacity <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" name="daily_capacity"
                           id="edit_daily_capacity" min="1" max="999" value="50" required>
                    <div class="form-text">Max leads this staff can handle per day.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" name="update" class="btn btn-warning">Update Staff</button>
            </div>
        </form>
    </div>
</div>

<?php include('scripts.php'); ?>

<script>
document.querySelectorAll(".editBtn").forEach(btn => {
    btn.addEventListener("click", function () {
        document.getElementById("edit_id").value               = this.dataset.id;
        document.getElementById("edit_name").value             = this.dataset.name;
        document.getElementById("edit_username").value         = this.dataset.username;
        document.getElementById("edit_usergroup").value        = this.dataset.usergroup;
        document.getElementById("edit_daily_capacity").value   = this.dataset.capacity;
        document.getElementById("edit_password").value         = '';
        document.getElementById("edit_confirm_password").value = '';
    });
});

function validatePassword() {
    const pw  = document.getElementById('edit_password').value;
    const cpw = document.getElementById('edit_confirm_password').value;
    if (pw !== cpw)                      { alert('Passwords do not match!'); return false; }
    if (pw.length > 0 && pw.length < 6) { alert('Password must be at least 6 characters!'); return false; }
    return true;
}
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        if (this.querySelector('[name="password"]') && this.querySelector('[name="confirm_password"]')) {
            if (!validatePassword()) e.preventDefault();
        }
    });
});
</script>

</body>
</html>