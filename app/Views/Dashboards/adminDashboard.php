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
    <title>Staff - Dashboard</title>
</head>
<body>
    <h1>Library Management System</h1>
    <h2><?php echo $role; ?> Dashboard</h2>
    <h2>Welcome <?php echo $fname; ?></h2>

    <div id="navigation">
        <h2>Dashboard</h2>
    </div>

    <button onclick="window.location.href='app/Views/Admin/addStaff.php'">Add Staff</button>
    <button onclick="window.location.href='app/Views/Admin/addMember.php'">Add Member</button>
    <button onclick="window.location.href='app/Views/Admin/addMaterial.php'">Add Material</button>
    <button onclick="window.location.href='app/Views/Circulation/manageBorrows.php'">Manage Borrow Requests</button>
    <button onclick="window.location.href='app/Views/Dashboards/analystDashboard.php'">Analytics</button>
    <button onclick="logout()">Logout</button>

    <script src="app/Views/Auth/logAuth.js"></script>
</body>
</html>