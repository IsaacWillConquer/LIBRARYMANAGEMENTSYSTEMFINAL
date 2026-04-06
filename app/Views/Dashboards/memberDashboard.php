<?php
require_once __DIR__ . "/../../../core/auth.php";

mustBeMember();

if (isDefaultPassword()) {
    header('Location: /../Auth/changePass.php');
    exit();
}

$fname = $_SESSION['FullName'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/LIBRARYMANAGEMENTSYSTEMFINAL/">
    <script src="core/jq.js"></script>
    <link rel="stylesheet" href="app/Views/Member/memberDashboard.css">
    <title>Member Dashboard</title>
</head>
<body>

<header>
    <h1>Nyle's Library Management System</h1>
    <div>
        <div class="notif-wrap" style="display:inline-block;">
            <button class="nav-btn" onclick="toggleNotifications()">Notifications</button>
            <span id="unreadBadge"></span>
        </div>
        <a href="app/Views/Member/borrowHistory.php">My History</a>
        <button class="btn-logout" onclick="logout()">Logout</button>
    </div>
</header>

<div class="welcome">Welcome, <strong><?php echo htmlspecialchars($fname); ?></strong></div>

<div id="error_text"></div>
<div id="success_text"></div>

<div id="notificationsPanel">
    <div class="notif-head">
        Notifications
        <button class="btn-markread" onclick="markAllRead()">Mark all read</button>
    </div>
    <div class="notif-body" id="notifBody">
        <div class="notif-row">Loading...</div>
    </div>
</div>

<main>

    <h2>My Active Borrows</h2>
    <table id="myBorrowsTable">
        <thead>
            <tr>
                <th>Title</th><th>Type</th><th>Borrow Date</th><th>Due Date</th><th>Status</th>
            </tr>
        </thead>
        <tbody><tr><td colspan="5">Loading...</td></tr></tbody>
    </table>

    <h2>My Requests</h2>
    <table id="myRequestsTable">
        <thead>
            <tr>
                <th>Title</th><th>Type</th><th>Request Date</th><th>Status</th><th>Action</th>
            </tr>
        </thead>
        <tbody><tr><td colspan="5">Loading...</td></tr></tbody>
    </table>

    <h2>Browse Materials</h2>


    <input type="text" id="matSearch" placeholder="Search by title, author, genre...">
    <div class="tab-bar">
        <button class="tab-btn active" onclick="showTab('trending', this)">Trending</button>
        <button class="tab-btn" onclick="showTab('recommended', this)">Recommended</button>
        <button class="tab-btn" onclick="showTab('all', this)">All Available</button>
    </div>

    <div id="paneTrending">
        <table id="trendingTable">
            <thead>
                <tr><th>Title</th><th>Author</th><th>Type</th><th>Genre</th><th>Available</th><th>Action</th></tr>
            </thead>
            <tbody><tr><td colspan="6">Loading...</td></tr></tbody>
        </table>
    </div>

    <div id="paneRecommended" style="display:none;">
        <table id="recommendedTable">
            <thead>
                <tr><th>Title</th><th>Author</th><th>Type</th><th>Genre</th><th>Available</th><th>Action</th></tr>
            </thead>
            <tbody><tr><td colspan="6">Loading...</td></tr></tbody>
        </table>
    </div>

    <div id="paneAll" style="display:none;">
        <table id="allTable">
            <thead>
                <tr><th>Title</th><th>Author</th><th>Type</th><th>Genre</th><th>Available</th><th>Action</th></tr>
            </thead>
            <tbody><tr><td colspan="6">Loading...</td></tr></tbody>
        </table>
    </div>

</main>

<script src="app/Views/Auth/logAuth.js"></script>
<script src="app/Views/Member/logBorrow.js?v=3"></script>

</body>
</html>