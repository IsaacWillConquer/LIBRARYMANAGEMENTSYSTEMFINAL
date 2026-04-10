<?php
require_once __DIR__ . "/../../../core/auth.php";
mustBeStaff();

if (isDefaultPassword()) {
    header('Location: /../Auth/changePass.php');
    exit();
}

$role  = $_SESSION['Role'];
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
    <link rel="stylesheet" href="app/Views/CSS/analyist.css">
    <title>Analytics Dashboard</title>

</head>
<body>


<header>
    <h1>Nyle's Library Management System</h1>
    <div style="display:flex;align-items:center;gap:8px;">
        <span><?php echo $role; ?> — <?php echo htmlspecialchars($fname); ?></span>
        <a class="btn-back" href="app/Views/Dashboards/adminDashboard.php">← Dashboard</a>
        <button class="btn-logout" onclick="logout()">Logout</button>
        <a href="app/Views/Dashboards/logArchives.php" class="btn-refresh" style="text-decoration:none;float:left;">🗄️ Logs & Archives</a>
    </div>
</header>


<div class="subbar">
    <span>Analytics Dashboard</span>
    <span id="lastUpdated" style="font-size:.75rem;color:#6a8;">Loaded just now</span>
</div>


<main>
    <button class="btn-refresh" onclick="loadAll()">↻ Refresh</button>
    <div style="clear:both;"></div>

    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-label">Total Members</div>
            <div class="stat-value" id="statMembers">—</div>
        </div>
        <div class="stat-card green">
            <div class="stat-label">Total Materials</div>
            <div class="stat-value" id="statMaterials">—</div>
        </div>
        <div class="stat-card teal">
            <div class="stat-label">Active Borrows</div>
            <div class="stat-value" id="statActive">—</div>
        </div>
        <div class="stat-card red">
            <div class="stat-label">Overdue</div>
            <div class="stat-value" id="statOverdue">—</div>
        </div>
        <div class="stat-card orange">
            <div class="stat-label">Fines Collected</div>
            <div class="stat-value" id="statFines">—</div>
        </div>
        <div class="stat-card purple">
            <div class="stat-label">Pending Requests</div>
            <div class="stat-value" id="statPending">—</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Pending Donations</div>
            <div class="stat-value" id="statDonations">—</div>
        </div>
    </div>


    <div class="section-title">Borrow Activity (Last 7 Days)</div>
    <div class="chart-row cols-1">
        <div class="chart-card">
            <h4>Borrows per Month</h4>
            <canvas id="chartBorrowsPerMonth" height="90"></canvas>
        </div>
    </div>

    <div class="section-title">What's Being Read</div>
    <div class="chart-row cols-2">
        <div class="chart-card">
            <h4>Top 5 Most Borrowed</h4>
            <canvas id="chartTopBorrowed" height="200"></canvas>
        </div>
        <div class="chart-card">
            <h4>Top Genres by Borrow Count</h4>
            <canvas id="chartGenre" height="200"></canvas>
        </div>
    </div>

    <div class="section-title">Collection Overview</div>
    <div class="chart-row cols-3">
        <div class="chart-card">
            <h4>Borrow Status Breakdown</h4>
            <canvas id="chartStatusBreakdown" height="200"></canvas>
        </div>
        <div class="chart-card">
            <h4>Material Type Distribution</h4>
            <canvas id="chartMaterialTypes" height="200"></canvas>
        </div>
        <div class="chart-card">
            <h4>Top 5 Most Active Members</h4>
            <table class="mini-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Member</th>
                        <th>Borrows</th>
                    </tr>
                </thead>
                <tbody id="topMembersBody">
                    <tr>
                        <td colspan="3" style="color:#aaa;">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="section-title">Recent Activity</div>
    <div class="activity-feed">
        <h4>Last 10 Actions</h4>
        <div id="activityFeed"><div class="activity-item" style="color:#aaa;">Loading...</div></div>
    </div>

</main>

<script src="app/Views/Auth/logAuth.js"></script>
<script src="app/Views/Dashboards/logAnalytics.js"></script>

</body>
</html>