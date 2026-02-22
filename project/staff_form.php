<?php
// Determine if we're in edit mode
$is_edit = isset($_POST['update']) || (isset($_GET['edit']) && $_GET['edit'] == 'true');
?>
<div class="mb-3">
    <label class="form-label">Name <span class="text-danger">*</span></label>
    <input type="text" class="form-control" name="name" id="<?= $is_edit ? 'edit_name' : 'name' ?>" 
           value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>" required>
</div>

<div class="mb-3">
    <label class="form-label">Username <span class="text-danger">*</span></label>
    <input type="text" class="form-control" name="username" id="<?= $is_edit ? 'edit_username' : 'username' ?>" 
           value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" required>
</div>

<div class="mb-3">
    <label class="form-label">Password <?= $is_edit ? '' : '<span class="text-danger">*</span>' ?></label>
    <input type="password" class="form-control" name="password" id="<?= $is_edit ? 'edit_password' : 'password' ?>" 
           <?= $is_edit ? 'placeholder="Leave blank to keep current password"' : 'required' ?>>
</div>

<div class="mb-3">
    <label class="form-label">Confirm Password <?= $is_edit ? '' : '<span class="text-danger">*</span>' ?></label>
    <input type="password" class="form-control" name="confirm_password" id="<?= $is_edit ? 'edit_confirm_password' : 'confirm_password' ?>" 
           <?= $is_edit ? 'placeholder="Leave blank to keep current password"' : 'required' ?>>
</div>

<div class="mb-3">
    <label class="form-label">User Group <span class="text-danger">*</span></label>
    <select class="form-control" name="usergroup" id="<?= $is_edit ? 'edit_usergroup' : 'usergroup' ?>" required>
        <option value="1" <?= (isset($_POST['usergroup']) && $_POST['usergroup'] == 1) ? 'selected' : '' ?>>Admin</option>
        <option value="2" <?= (isset($_POST['usergroup']) && $_POST['usergroup'] == 2) ? 'selected' : '' ?>>Staff</option>
    </select>
</div>