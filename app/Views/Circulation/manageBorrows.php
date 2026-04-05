<?php
    require_once __DIR__ . "/../../../core/auth.php";

    mustBeStaff();

    if (isDefaultPassword()) {
        header('Location: /../Auth/changePass.php');
        exit();
    }

    $fname = $_SESSION['FullName'];
    $role  = $_SESSION['Role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/LIBRARYMANAGEMENTSYSTEMFINAL/">
    <script src="core/jq.js"></script>
    <title>Manage Borrow Requests</title>
</head>
<body>

<h1>Library Management System</h1>
<h2><?php echo $role; ?> - Manage Borrow Requests</h2>
<h2>Welcome <?php echo $fname; ?></h2>

<div id="navigation">
    <h2>Dashboard</h2>
</div>

<div id="error_text" style="color:red; display:none;"></div>
<div id="success_text" style="color:green; display:none;"></div>

<h2>Pending Borrow Requests</h2>
<table id="pendingTable" border="1">
    <thead>
        <tr>
            <th>Member</th>
            <th>Title</th>
            <th>Type</th>
            <th>Author</th>
            <th>Request Date</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <tr><td colspan="6">Loading...</td></tr>
    </tbody>
</table>

<h2>Active Borrows</h2>
<table id="activeBorrowsTable" border="1">
    <thead>
        <tr>
            <th>Member</th>
            <th>Title</th>
            <th>Type</th>
            <th>Borrow Date</th>
            <th>Due Date</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <tr><td colspan="7">Loading...</td></tr>
    </tbody>
</table>

<button onclick="location.href='app/Views/Dashboards/adminDashboard.php'">Go Back</button>

<script src="app/Views/Auth/logAuth.js"></script>
<script src="app/Views/Circulation/logCirculation.js"></script>

</body>
</html>