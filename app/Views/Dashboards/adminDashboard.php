<?php
require_once __DIR__ . "/../../../core/auth.php";

mustBeStaff();
adminOnly();

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
    <link rel="stylesheet" href="app/Views/CSS/analyist.css">

    <title>Admin Dashboard</title>
</head>
<body>

<header>
    <h1>Library Management System</h1>
    <div class="header-right">
        <span><?php echo $role; ?> — <?php echo htmlspecialchars($fname); ?></span>
        <button class="btn-logout" onclick="logout()">Logout</button>
    </div>
</header>

<div class="subbar">
    <span>Admin Dashboard</span>
    <span style="font-size:.75rem;color:#aaa;" id="dateNow"></span>
</div>

<main>

    <div class="section-label">Overview</div>
    <div class="stat-row">
        <div class="stat-card">
            <div class="sv" id="statMembers">—</div>
            <div class="sl">Members</div>
        </div>
        <div class="stat-card green">
            <div class="sv" id="statMaterials">—</div>
            <div class="sl">Materials</div>
        </div>
        <div class="stat-card teal">
            <div class="sv" id="statActive">—</div>
            <div class="sl">Active Borrows</div>
        </div>
        <div class="stat-card red">
            <div class="sv" id="statOverdue">—</div>
            <div class="sl">Overdue</div>
        </div>
        <div class="stat-card orange">
            <div class="sv" id="statPending">—</div>
            <div class="sl">Pending Requests</div>
        </div>
        <div class="stat-card">
            <div class="sv" id="statDonations">—</div>
            <div class="sl">Pending Donations</div>
        </div>
    </div>

    <div class="section-label">Management</div>
    <div class="nav-grid">
        <a class="nav-card" href="app/Views/Admin/addStaff.php">Staff</a>
        <a class="nav-card" href="app/Views/Admin/addMember.php">Members</a>
        <a class="nav-card" href="app/Views/Admin/addMaterial.php">Materials</a>
        <a class="nav-card" href="app/Views/Circulation/manageBorrows.php">Borrow Requests</a>
        <a class="nav-card" href="app/Views/Dashboards/analystDashboard.php">Analytics</a>
        <a class="nav-card" href="app/Views/Dashboards/logArchives.php">Logs & Archives</a>
    </div>  



    <div class="section-title">Recent Activity</div>
    <div class="activity-feed">
        <h4>Last 10 Actions</h4>
        <div id="activityFeed"><div class="activity-item" style="color:#aaa;">Loading...</div></div>
    </div>







</main>

<script src="app/Views/Auth/logAuth.js"></script>
<script src="app/Views/Dashboards/logAnalytics.js"></script>

<script>
document.getElementById('dateNow').textContent = new Date().toLocaleDateString('en-PH', {
    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
});

$.ajax({
    url: 'app/Controllers/analyticsController.php',
    method: 'GET',
    data: { action: 'getSummary' },
    dataType: 'json',
    success: function(data) {
        $('#statMembers').text(data.totalMembers);
        $('#statMaterials').text(data.totalMaterials);
        $('#statActive').text(data.activeBorrows);
        $('#statOverdue').text(data.overdueCount);
        $('#statPending').text(data.pendingReqs);
        $('#statDonations').text(data.pendingDonations);
    }
});
</script>
</body>
</html>