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
    <title>Analytics Dashboard</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; color: #222; font-size: .85rem; }

        header {
            background: #1a2744; color: #fff;
            padding: 0 20px; height: 50px;
            display: flex; align-items: center; justify-content: space-between;
        }
        header h1 { font-size: 1rem; }
        header span { color: #aac; font-size: .82rem; }
        .btn-logout {
            background: #fff; color: #1a2744; border: none;
            border-radius: 3px; padding: 4px 10px; font-size: .82rem;
            cursor: pointer; font-family: Arial, sans-serif; margin-left: 12px;
        }
        .btn-back {
            background: none; color: #aac; border: 1px solid #3a4a6a;
            border-radius: 3px; padding: 4px 10px; font-size: .82rem;
            cursor: pointer; font-family: Arial, sans-serif;
            text-decoration: none;
        }

        .subbar {
            background: #243358; color: #ccc;
            padding: 7px 20px; font-size: .82rem;
            display: flex; align-items: center; justify-content: space-between;
        }

        main { padding: 16px 20px; max-width: 1200px; margin: 0 auto; }

        /* stat cards */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 12px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: #fff; border-radius: 5px;
            padding: 14px 16px;
            border-left: 4px solid #4e73df;
        }
        .stat-card.green  { border-left-color: #1cc88a; }
        .stat-card.red    { border-left-color: #e74a3b; }
        .stat-card.orange { border-left-color: #f6c23e; }
        .stat-card.teal   { border-left-color: #36b9cc; }
        .stat-card.purple { border-left-color: #6f42c1; }
        .stat-label { font-size: .72rem; color: #888; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 6px; }
        .stat-value { font-size: 1.5rem; font-weight: bold; color: #1a2744; }

        /* section title */
        .section-title {
            font-size: .9rem; font-weight: bold; color: #1a2744;
            margin: 20px 0 10px; padding-bottom: 6px;
            border-bottom: 2px solid #e0e4ec;
        }

        /* chart grid */
        .chart-row {
            display: grid;
            gap: 16px;
            margin-bottom: 20px;
        }
        .chart-row.cols-2 { grid-template-columns: 1fr 1fr; }
        .chart-row.cols-3 { grid-template-columns: 1fr 1fr 1fr; }
        .chart-row.cols-1 { grid-template-columns: 1fr; }

        .chart-card {
            background: #fff; border-radius: 5px;
            padding: 16px; overflow: hidden;
        }
        .chart-card h4 {
            font-size: .82rem; color: #555; margin-bottom: 12px;
            font-weight: bold; text-transform: uppercase; letter-spacing: .04em;
        }
        .chart-card canvas { width: 100% !important; }

        /* activity feed */
        .activity-feed { background: #fff; border-radius: 5px; padding: 16px; }
        .activity-feed h4 { font-size: .82rem; color: #555; margin-bottom: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: .04em; }
        .activity-item {
            display: flex; align-items: flex-start; gap: 10px;
            padding: 7px 0; border-bottom: 1px solid #f0f0f0; font-size: .82rem;
        }
        .activity-item:last-child { border-bottom: none; }
        .activity-action {
            font-size: .7rem; font-weight: bold; padding: 2px 7px;
            border-radius: 3px; white-space: nowrap; flex-shrink: 0;
        }
        .a-Requested   { background: #fef3e2; color: #a05c00; }
        .a-Approved    { background: #edf7f1; color: #1a7a4a; }
        .a-Rejected    { background: #fdecea; color: #c0392b; }
        .a-Returned    { background: #dce8f7; color: #1a4a8a; }
        .a-Cancelled   { background: #f0f0f0; color: #888; }
        .a-Claimed     { background: #e8f4fd; color: #0e6ba8; }
        .a-Unclaimed   { background: #fff0e0; color: #b85c00; }
        .a-MarkedOverdue { background: #fdecea; color: #c0392b; }
        .activity-text { color: #444; line-height: 1.4; }
        .activity-time { font-size: .72rem; color: #aaa; margin-top: 1px; }

        /* top members table */
        .mini-table { width: 100%; border-collapse: collapse; font-size: .83rem; }
        .mini-table th { text-align: left; color: #888; font-size: .72rem; text-transform: uppercase; padding: 0 0 6px; border-bottom: 1px solid #eee; }
        .mini-table td { padding: 6px 0; border-bottom: 1px solid #f5f5f5; }
        .mini-table tr:last-child td { border-bottom: none; }
        .rank { font-weight: bold; color: #1a2744; width: 24px; }

        /* refresh button */
        .btn-refresh {
            background: #1a2744; color: #fff; border: none; border-radius: 3px;
            padding: 5px 12px; font-size: .78rem; cursor: pointer;
            font-family: Arial, sans-serif; float: right; margin-bottom: 12px;
        }
        .btn-refresh:hover { background: #243358; }
    </style>
</head>
<body>

<header>
    <h1>Nyle's Library Management System</h1>
    <div style="display:flex;align-items:center;gap:8px;">
        <span><?php echo $role; ?> — <?php echo htmlspecialchars($fname); ?></span>
        <a class="btn-back" href="app/Views/Dashboards/adminDashboard.php">← Dashboard</a>
        <button class="btn-logout" onclick="logout()">Logout</button>
    </div>
</header>

<div class="subbar">
    <span>Analytics Dashboard</span>
    <span id="lastUpdated" style="font-size:.75rem;color:#6a8;">Loaded just now</span>
</div>

<main>

    <button class="btn-refresh" onclick="loadAll()">↻ Refresh</button>
    <div style="clear:both;"></div>

    <!-- summary cards -->
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

    <!-- row 1: borrow activity line chart (full width) -->
    <div class="section-title">Borrow Activity (Last 6 Months)</div>
    <div class="chart-row cols-1">
        <div class="chart-card">
            <h4>Borrows per Month</h4>
            <canvas id="chartBorrowsPerMonth" height="90"></canvas>
        </div>
    </div>

    <!-- row 2: top borrowed + genre -->
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

    <!-- row 3: status breakdown + material types -->
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
                <thead><tr><th>#</th><th>Member</th><th>Borrows</th></tr></thead>
                <tbody id="topMembersBody"><tr><td colspan="3" style="color:#aaa;">Loading...</td></tr></tbody>
            </table>
        </div>
    </div>

    <!-- row 4: recent activity feed -->
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