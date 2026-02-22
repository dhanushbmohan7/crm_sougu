<?php
require("../include/session_chk.php");
include "../include/library.php";

$username = $_SESSION['username'] ?? 'User';
$obj = new Library();

// Handle CREATE
if (isset($_POST['create'])) {
    $name = $_POST['status_name'];
    $key = $_POST['status_key'];
    $sort = $_POST['sort_order'];
    $color = $_POST['color'];
    $active = $_POST['is_active'];

    $sql = "INSERT INTO lead_status_master (status_name, status_key, sort_order, color, is_active)
            VALUES ('$name', '$key', '$sort', '$color', '$active')";
    $obj->generalquery($sql);
    header("Location: lead_status.php?msg=created");
    exit;
}

// Handle UPDATE
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = $_POST['status_name'];
    $key = $_POST['status_key'];
    $sort = $_POST['sort_order'];
    $color = $_POST['color'];
    $active = $_POST['is_active'];

    $sql = "UPDATE lead_status_master 
            SET status_name='$name', status_key='$key', sort_order='$sort', color='$color', is_active='$active'
            WHERE id=$id";
    $obj->generalquery($sql);
    header("Location: lead_status.php?msg=updated");
    exit;
}

// Handle DELETE
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM lead_status_master WHERE id=$id";
    $obj->generalquery($sql);
    header("Location: lead_status.php?msg=deleted");
    exit;
}

// Fetch all statuses
$data = $obj->generalquery("SELECT * FROM lead_status_master ORDER BY sort_order ASC");
$data=mysqli_fetch_all($data);


?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>CRM - Lead Status Master</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<style>
body { background: #f5f6fa; }
.color-box { width: 30px; height: 30px; border-radius: 5px; display: inline-block; border: 1px solid #ccc; }
.page-container {
    margin-left: 250px;
    transition: 0.3s;
    padding: 20px;
}
</style>
</head>
<body>

<?php include('header.php') ?>

<div class="page-container">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Lead Status Master</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
            <i class="bi bi-plus-circle"></i> Add Status
        </button>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success">Action Completed Successfully</div>
    <?php endif; ?>

    <table class="table table-bordered table-striped">
    <thead>
    <tr class="table-dark">
        <th>#</th>
        <th>Status Name</th>
        <th>Status Key</th>
        <th>Sort Order</th>
        <th>Color</th>
        <th>Active</th>
        <th width="140">Actions</th>
    </tr>
    </thead>

    <tbody>
    <?php 
    if (!empty($data)) {
        foreach ($data as $row) { 
    ?>
        <tr>
            <td><?= $row[0] ?></td>
            <td><?= htmlspecialchars($row[1]) ?></td>
            <td><span class="badge bg-secondary"><?= $row[2] ?></span></td>
            <td><span class="badge bg-info"><?= $row[3] ?></span></td>
            <td>
                <span style="
                    background: <?= $row[4] ?>; 
                    display:inline-block;
                    width:28px; 
                    height:28px; 
                    border-radius:5px; 
                    border:1px solid #ccc;">
                </span>
            </td>
            <td>
                <?php if ($row[5] == 1): ?>
                    <span class="badge bg-success">Active</span>
                <?php else: ?>
                    <span class="badge bg-danger">Inactive</span>
                <?php endif; ?>
            </td>

            <td>
                <button class="btn btn-sm btn-warning editBtn"
                    data-id="<?= $row[0] ?>"
                    data-name="<?= $row[1] ?>"
                    data-key="<?= $row[2] ?>"
                    data-sort="<?= $row[3] ?>"
                    data-color="<?= $row[4] ?>"
                    data-active="<?= $row[5] ?>"
                    data-bs-toggle="modal"
                    data-bs-target="#editModal">
                    <i class="bi bi-pencil-square"></i>
                </button>

                <a href="?delete=<?= $row[0] ?>"
                    onclick="return confirm('Delete this status?')"
                    class="btn btn-sm btn-danger">
                    <i class="bi bi-trash"></i>
                </a>
            </td>
        </tr>

    <?php 
        }
    } 
    ?>
    </tbody>
</table>

</div>

<!-- Create Modal -->
<div class="modal fade" id="createModal">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5>Add Lead Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php include "status_form.php"; ?>
            </div>
            <div class="modal-footer">
                <button type="submit" name="create" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <div class="modal-header bg-warning">
                <h5>Edit Lead Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="edit_id">
                <?php include "status_form.php"; ?>
            </div>
            <div class="modal-footer">
                <button type="submit" name="update" class="btn btn-warning">Update</button>
            </div>
        </form>
    </div>
</div>

<?php include('scripts.php'); ?>

<script>
document.querySelectorAll(".editBtn").forEach(btn => {
    btn.addEventListener("click", function () {
        document.getElementById("edit_id").value = this.dataset.id;
        document.querySelector("[name='status_name']").value = this.dataset.name;
        document.querySelector("[name='status_key']").value = this.dataset.key;
        document.querySelector("[name='sort_order']").value = this.dataset.sort;
        document.querySelector("[name='color']").value = this.dataset.color;
        document.querySelector("[name='is_active']").value = this.dataset.active;
    });
});
</script>

</body>
</html>
