<?php
require_once __DIR__ . "/../../../core/auth.php";
mustBeStaff();

if (isDefaultPassword()) {
    header('Location: /../Auth/changePass.php');
    exit();
}

$role = $_SESSION['Role'];
$fname = $_SESSION['FullName'];

if ($role !== 'Admin' && $role !== 'DataAnalyst') {
    header('Location: /LIBRARYMANAGEMENTSYSTEMFINAL/index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/LIBRARYMANAGEMENTSYSTEMFINAL/">
    <script src="core/jq.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Analytics Dashboard</title>
</head>
<body>

    <h1>Library Management System</h1>
    <h2><?php echo $role; ?> - Analytics Dashboard</h2>
    <h2>Welcome <?php echo $fname; ?></h2>

    <div id="navigation">
        <h2>Dashboard</h2>
    </div>

    <button onclick="logout()">Logout</button>
    <br><br>

    <div id="summaryCards">
        <span>Total Members: <strong id="statMembers">...</strong></span> &nbsp;|&nbsp;
        <span>Total Materials: <strong id="statMaterials">...</strong></span> &nbsp;|&nbsp;
        <span>Active Borrows: <strong id="statActive">...</strong></span> &nbsp;|&nbsp;
        <span>Overdue: <strong id="statOverdue" style="color:red;">...</strong></span>
    </div>
    <br><br>

    <h3>Borrow Activity</h3>
    <canvas id="chartBorrowsPerMonth" width="600" height="300"></canvas>
    <br><br>

    <h3>Top 5 Most Borrowed Materials</h3>
    <canvas id="chartTopBorrowed" width="600" height="300"></canvas>
    <br><br>

    <h3>Borrow Status Breakdown</h3>
    <canvas id="chartStatusBreakdown" width="300" height="300"></canvas>
    &nbsp;&nbsp;&nbsp;

    <h3>Material Type Distribution</h3>
    <canvas id="chartMaterialTypes" width="300" height="300"></canvas>
    <br><br>

    <script src="app/Views/Auth/logAuth.js"></script>
    <script src="app/Views/Dashboards/logAnalytics.js"></script>

</body>
</html>