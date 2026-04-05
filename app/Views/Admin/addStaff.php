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
    <title>Admin - Add Staff</title>
</head>
<body>
    <h1>Library Management System</h1>

    <div id="navigation">
        <h2>Staff Management</h2>
    </div>

    <button id="toggleBtn">Add Staff</button>

    <div id="staffForm">
        <div id="error_text" style="color:red; display:none;"></div>

        <h3>Add Staff</h3>

        <input type="text" id="fname" placeholder="First Name"><br><br>
        <input type="text" id="lname" placeholder="Last Name"><br><br>
        <input type="email" id="email" placeholder="Email"><br><br>

        <label>Role</label>
        <select id="addRole"></select><br><br>

        <label>Status</label>
        <select id="addStatus"></select><br><br>

        <button onclick="addStaff()">Save</button>
        <button onclick="$('#staffForm').hide()">Cancel</button>
    </div>

    <div id="editForm" style="display:none;">
        <h3>Edit Staff</h3>

        <input type="hidden" id="editStaffID">
        <input type="text" id="editFname" placeholder="First Name"><br><br>
        <input type="text" id="editLname" placeholder="Last Name"><br><br>
        <input type="email" id="editEmail" placeholder="Email"><br><br>

        <label>Role</label>
        <select id="editRole"></select><br><br>

        <label>Status</label>
        <select id="editStatus"></select><br><br>

        <button onclick="updStaff()">Update</button>
        <button onclick="$('#editForm').hide()">Cancel</button>
    </div>

    <input type="text" id="searchInput" placeholder="Search staff..." onkeyup="doStaffSearch(this.value.trim())">

    <table id="staffTable" border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Default Pass</th>
                <th>Date Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <button onclick="location.href='app/Views/Dashboards/adminDashboard.php'">Go Back</button>

    <script src="app/Views/Auth/logAuth.js"></script>
    <script src="app/Views/Admin/logStaff.js"></script>
</body>
</html>