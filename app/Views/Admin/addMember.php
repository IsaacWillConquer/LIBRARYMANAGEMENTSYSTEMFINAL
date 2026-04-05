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
    <title>Staff - Add Member</title>
</head>
<body>

    <h1>Library Management System</h1>

    <div id="navigation">
        <h2>Member Management</h2>
    </div>

    <button id="toggleBtn">Add Member</button>

    <div id="memberForm">
        <div id="error_text" style="color:red; display:none;"></div>

        <h3>Add Member</h3>

        <input type="text" id="fname" placeholder="First Name"><br><br>
        <input type="text" id="lname" placeholder="Last Name"><br><br>
        <input type="email" id="email" placeholder="Email"><br><br>

        <label>Status</label>
        <select id="addStatus"></select><br><br>

        <button onclick="addMember()">Save</button>
        <button onclick="$('#memberForm').hide()">Cancel</button>
    </div>

    <div id="editForm" style="display:none;">
        <h3>Edit Member</h3>

        <input type="hidden" id="editMemberID">
        <input type="text" id="editFname" placeholder="First Name"><br><br>
        <input type="text" id="editLname" placeholder="Last Name"><br><br>
        <input type="email" id="editEmail" placeholder="Email"><br><br>

        <button onclick="updMember()">Update</button>
        <button onclick="$('#editForm').hide()">Cancel</button>
    </div>

    <input type="text" id="searchInput" placeholder="Search members..." onkeyup="doMemberSearch(this.value.trim())">

    <table id="memberTable" border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
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
    <script src="app/Views/Admin/logMember.js"></script>

</body>
</html>