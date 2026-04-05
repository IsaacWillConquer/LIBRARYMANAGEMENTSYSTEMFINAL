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
    <title>Notifications</title>
</head>
<body>

<h1>Library Management System</h1>
<h2>Welcome <?php echo $fname; ?></h2>

<div id="navigation">
    <a href="app/Views/Dashboards/memberDashboard.php">Dashboard</a>
</div>

<h2>Notifications</h2>
<button onclick="markAllRead()">Mark All as Read</button>

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

<button onclick="logout()">Logout</button>

<script src="app/Views/Auth/logAuth.js"></script>
<script src="app/Views/Member/logNotifications.js"></script>

</body>
</html>