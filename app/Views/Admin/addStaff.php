<?php
require_once __DIR__ . "/../../../core/auth.php";

mustBeStaff();

if (isDefaultPassword()) {
    header('Location: /../Auth/changePass.php');
    exit();
}

$role = $_SESSION['Role'];
$fname = $_SESSION['FullName'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/LIBRARYMANAGEMENTSYSTEMFINAL/">
    <script src="core/jq.js"></script>
    <link rel="stylesheet" href="app/Views/CSS/generalize.css">
    <title>Admin - Staff Management</title>
</head>
<body>


<header>
    <h1>Nyle's Library Management System</h1>
    <div class="header-right">
        <span><?php echo $role; ?> — <?php echo htmlspecialchars($fname); ?></span>
        <a class="btn btn-ghost" href="app/Views/Dashboards/adminDashboard.php">← Dashboard</a>
        <button class="btn-logout" onclick="logout()">Logout</button>
    </div>
</header>

<div class="subbar"><span>Staff Management</span></div>

<main>
    <div id="error_text"></div>

    <button class="btn-primary" id="toggleBtn" style="margin-bottom:10px;">+ Add Staff</button>

    <div id="staffForm" class="form-box" style="display:none;">
        <h3>Add Staff</h3>
        <div class="form-row"><label>First Name</label><input type="text" id="fname" placeholder="First Name"></div>
        <div class="form-row"><label>Last Name</label><input type="text" id="lname" placeholder="Last Name"></div>
        <div class="form-row"><label>Email</label><input type="email" id="email" placeholder="Email"></div>
        <div class="form-row"><label>Role</label><select id="addRole"></select></div>
        <div class="form-row"><label>Status</label><select id="addStatus"></select></div>
        <div style="display:flex;gap:8px;margin-top:10px;">
            <button class="btn-primary" onclick="addStaff()">Save</button>
            <button class="btn btn-ghost" style="color:#555;border-color:#ccc;" onclick="$('#staffForm').hide()">Cancel</button>
        </div>
    </div>

    <div id="editForm" class="form-box" style="display:none;">
        <h3>Edit Staff</h3>
        <input type="hidden" id="editStaffID">
        <div class="form-row"><label>First Name</label><input type="text" id="editFname" placeholder="First Name"></div>
        <div class="form-row"><label>Last Name</label><input type="text" id="editLname" placeholder="Last Name"></div>
        <div class="form-row"><label>Email</label><input type="email" id="editEmail" placeholder="Email"></div>
        <div class="form-row"><label>Role</label><select id="editRole"></select></div>
        <div class="form-row"><label>Status</label><select id="editStatus"></select></div>
        <div style="display:flex;gap:8px;margin-top:10px;">
            <button class="btn-primary" onclick="updStaff()">Update</button>
            <button class="btn btn-ghost" style="color:#555;border-color:#ccc;" onclick="$('#editForm').hide()">Cancel</button>
        </div>
    </div>

    <input type="text" class="tbl-search" id="searchInput" placeholder="Search staff..." onkeyup="doStaffSearch(this.value.trim())">

    <table id="staffTable">
        <thead>
            <tr>
                <th>ID</th><th>Name</th><th>Email</th><th>Role</th>
                <th>Status</th><th>Default Pass</th><th>Date Created</th><th>Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</main>

<script src="app/Views/Auth/logAuth.js"></script>
<script src="app/Views/Admin/logStaff.js"></script>
</body>
</html>