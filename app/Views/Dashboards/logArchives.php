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

$backLink = ($role === 'Admin')
    ? 'app/Views/Dashboards/adminDashboard.php'
    : 'app/Views/Dashboards/analystDashboard.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/LIBRARYMANAGEMENTSYSTEMFINAL/">
    <script src="core/jq.js"></script>
    <link rel="stylesheet" href="app/Views/CSS/generalize.css">
    <link rel="stylesheet" href="app/Views/CSS/logArchives.css">
    <title>Logs & Archives</title>
    
</head>
<body>

<header>
    <h1>Library Management System</h1>
    <div class="header-right">
        <span><?php echo $role; ?> — <?php echo htmlspecialchars($fname); ?></span>
        <a href="<?php echo $backLink; ?>" class="btn" style="text-decoration:none;">← Back</a>
        <button class="btn-logout" onclick="logout()">Logout</button>
    </div>
</header>

<div class="subbar">
    <span>Logs & Archives</span>
    <span style="font-size:.75rem;color:#aaa;" id="dateNow"></span>
</div>

<main>

    <div class="page-tabs">
        <button class="page-tab active" onclick="switchTab('borrowLogs', this)">Borrow Logs</button>
        <button class="page-tab" onclick="switchTab('loginLogs', this)">Login Logs</button>
        <button class="page-tab" onclick="switchTab('staffLogs', this)">Staff Logs</button>
        <button class="page-tab" onclick="switchTab('memberLogs', this)">Member Logs</button>
        <button class="page-tab" onclick="switchTab('materialLogs', this)">Material Logs</button>
        <button class="page-tab" onclick="switchTab('archivesStaff', this)">Staff Archives</button>
        <button class="page-tab" onclick="switchTab('archivesMember', this)">Member Archives</button>
    </div>

    <div class="page-pane active" id="pane-borrowLogs">
        <div class="section-label">Borrow Logs</div>
        <div class="log-toolbar">
            <input type="text" id="searchBorrowLogs" placeholder="Search member, material, action...">
            <span id="countBorrowLogs" style="font-size:.78rem;color:#888;"></span>
        </div>


        <div class="tbl-wrap">
            <table id="tblBorrowLogs">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date & Time</th>
                        <th>Action</th>
                        <th>Member</th>
                        <th>Staff</th>
                        <th>Material</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="empty-row">
                        <td colspan="6">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>



    <div class="page-pane" id="pane-loginLogs">
        <div class="section-label">Login Logs</div>
        <div class="log-toolbar">
            <input type="text" id="searchLoginLogs" placeholder="Search email, type, status...">
            <span id="countLoginLogs" style="font-size:.78rem;color:#888;"></span>
        </div>
        <div class="tbl-wrap">
            <table id="tblLoginLogs">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date & Time</th>
                        <th>User Type</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>IP Address</th>
                    </tr>
            </thead>
                <tbody>
                    <tr class="empty-row">
                        <td colspan="6">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>




    <div class="page-pane" id="pane-staffLogs">
        <div class="section-label">Staff Logs</div>
        <div class="log-toolbar">
            <input type="text" id="searchStaffLogs" placeholder="Search admin, staff, action...">
            <span id="countStaffLogs" style="font-size:.78rem;color:#888;"></span>
        </div>

        <div class="tbl-wrap">
            <table id="tblStaffLogs">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date & Time</th>
                        <th>Action</th>
                        <th>Done By (Admin)</th>
                        <th>Affected Staff</th>
                        <th>Role</th>
                    </tr>
                </thead>
                <tbody><tr class="empty-row"><td colspan="6">Loading...</td></tr></tbody>
            </table>
        </div>
    </div>










    <div class="page-pane" id="pane-memberLogs">
        <div class="section-label">Member Logs</div>
        <div class="log-toolbar">
            <input type="text" id="searchMemberLogs" placeholder="Search staff, member, action...">
            <span id="countMemberLogs" style="font-size:.78rem;color:#888;"></span>
        </div>
        <div class="tbl-wrap">
            <table id="tblMemberLogs">
                <thead><tr>
                    <th>#</th><th>Date & Time</th><th>Action</th>
                    <th>Done By (Staff)</th><th>Affected Member</th><th>Email</th>
                </tr></thead>
                <tbody><tr class="empty-row"><td colspan="6">Loading...</td></tr></tbody>
            </table>
        </div>
    </div>

    <!-- MATERIAL LOGS -->
    <div class="page-pane" id="pane-materialLogs">
        <div class="section-label">Material Logs</div>
        <div class="log-toolbar">
            <input type="text" id="searchMaterialLogs" placeholder="Search staff, material, action...">
            <span id="countMaterialLogs" style="font-size:.78rem;color:#888;"></span>
        </div>
        <div class="tbl-wrap">
            <table id="tblMaterialLogs">
                <thead><tr>
                    <th>#</th><th>Date & Time</th><th>Action</th>
                    <th>Done By (Staff)</th><th>Material</th>
                </tr></thead>
                <tbody><tr class="empty-row"><td colspan="5">Loading...</td></tr></tbody>
            </table>
        </div>
    </div>

    <!-- STAFF ARCHIVES -->
    <div class="page-pane" id="pane-archivesStaff">
        <div class="section-label">Archived Staff</div>
        <div class="log-toolbar">
            <input type="text" id="searchArchivesStaff" placeholder="Search staff ID, date...">
            <span id="countArchivesStaff" style="font-size:.78rem;color:#888;"></span>
        </div>
        <div class="tbl-wrap">
            <table id="tblArchivesStaff">
                <thead><tr>
                    <th>#</th><th>Date Removed</th><th>Entity Type</th>
                    <th>Entity ID</th><th>Archived Data</th><th></th>
                </tr></thead>
                <tbody><tr class="empty-row"><td colspan="6">Loading...</td></tr></tbody>
            </table>
        </div>
    </div>

    <!-- MEMBER ARCHIVES -->
    <div class="page-pane" id="pane-archivesMember">
        <div class="section-label">Archived Members</div>
        <div class="log-toolbar">
            <input type="text" id="searchArchivesMember" placeholder="Search member ID, date...">
            <span id="countArchivesMember" style="font-size:.78rem;color:#888;"></span>
        </div>
        <div class="tbl-wrap">
            <table id="tblArchivesMember">
                <thead><tr>
                    <th>#</th><th>Date Removed</th><th>Entity Type</th>
                    <th>Entity ID</th><th>Archived Data</th><th></th>
                </tr></thead>
                <tbody><tr class="empty-row"><td colspan="6">Loading...</td></tr></tbody>
            </table>
        </div>
    </div>

</main>

<!-- JSON Modal -->
<div id="jsonModal">
    <div id="jsonBox">
        <button id="closeJson" onclick="closeJson()">✕</button>
        <pre id="jsonContent"></pre>
    </div>
</div>

<script src="app/Views/Auth/logAuth.js"></script>
<script src="app/Views/Dashboards/logArchives.js"></script>
<script>
document.getElementById('dateNow').textContent = new Date().toLocaleDateString('en-PH', {
    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
});
</script>
</body>
</html>