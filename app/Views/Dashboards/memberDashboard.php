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
    <title>Member Dashboard</title>
</head>
<body>

<h1>Library Management System</h1>
<h2>Welcome <?php echo $fname; ?></h2>

<div id="navigation">
    <h2>Dashboard</h2>
    <button onclick="logout()">Logout</button>
    <button onclick="toggleNotifications()">Notifications <span id="unreadBadge" style="color:red;display:none;"></span></button>
    <button onclick="window.location.href='/LIBRARYMANAGEMENTSYSTEMFINAL/app/Views/Member/borrowHistory.php'">Borrow History</button>
</div>

<div id="notificationsPanel" style="display:none; border:1px solid #ccc; padding:10px;">
    <h3>Notifications <button onclick="markAllRead()">Mark All as Read</button></h3>
    <table id="notificationsTable" border="1">
        <thead>
            <tr>
                <th>Message</th>
                <th>Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <tr><td colspan="3">Loading...</td></tr>
        </tbody>
    </table>
</div>

<div id="error_text" style="color:red; display:none;"></div>
<div id="success_text" style="color:green; display:none;"></div>

<h2>My Active Borrows</h2>
<table id="myBorrowsTable" border="1">
    <thead>
        <tr>
            <th>Title</th>
            <th>Type</th>
            <th>Borrow Date</th>
            <th>Due Date</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <tr><td colspan="5">Loading...</td></tr>
    </tbody>
</table>

<br>

<h2>My Requests</h2>
<table id="myRequestsTable" border="1">
    <thead>
        <tr>
            <th>Title</th>
            <th>Type</th>
            <th>Request Date</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <tr><td colspan="5">Loading...</td></tr>
    </tbody>
</table>

<br>

<h2>Browse Materials</h2>

<button id="tabTrending" onclick="showTab('trending')">Trending</button>
<button id="tabRecommended" onclick="showTab('recommended')">Recommended</button>
<button id="tabAll" onclick="showTab('all')">All Available</button>

<div id="paneTrending">
    <h3>Trending</h3>
    <table id="trendingTable" border="1">
        <thead>
            <tr>
                <th>Title</th><th>Author</th><th>Type</th>
                <th>Genre</th><th>Available</th><th>Action</th>
            </tr>
        </thead>
        <tbody><tr><td colspan="6">Loading...</td></tr></tbody>
    </table>
</div>

<div id="paneRecommended" style="display:none;">
    <h3>Recommended For You</h3>
    <table id="recommendedTable" border="1">
        <thead>
            <tr>
                <th>Title</th><th>Author</th><th>Type</th>
                <th>Genre</th><th>Available</th><th>Action</th>
            </tr>
        </thead>
        <tbody><tr><td colspan="6">Loading...</td></tr></tbody>
    </table>
</div>

<div id="paneAll" style="display:none;">
    <h3>All Available Materials</h3>
    <table id="allTable" border="1">
        <thead>
            <tr>
                <th>Title</th><th>Author</th><th>Type</th>
                <th>Genre</th><th>Available</th><th>Action</th>
            </tr>
        </thead>
        <tbody><tr><td colspan="6">Loading...</td></tr></tbody>
    </table>
</div>

<script src="app/Views/Auth/logAuth.js"></script>
<script src="app/Views/Member/logBorrow.js?v=1"></script>

</body>
</html>