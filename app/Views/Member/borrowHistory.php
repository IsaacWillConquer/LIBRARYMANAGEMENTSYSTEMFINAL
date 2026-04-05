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
    <title>Borrow History</title>
</head>
<body>

<h1>Library Management System</h1>
<h2>Welcome <?php echo $fname; ?></h2>

<div id="navigation">
    <a href="app/Views/Dashboards/memberDashboard.php">Dashboard</a>
</div>

<h2>My Borrow History</h2>

<table id="historyTable" border="1">
    <thead>
        <tr>
            <th>Title</th>
            <th>Author</th>
            <th>Type</th>
            <th>Borrow Date</th>
            <th>Due Date</th>
            <th>Return Date</th>
            <th>Status</th>
            <th>Fine</th>
        </tr>
    </thead>
    <tbody>
        <tr><td colspan="8">Loading...</td></tr>
    </tbody>
</table>

<button onclick="logout()">Logout</button>

<script src="app/Views/Auth/logAuth.js"></script>
<script src="app/Views/Member/logBorrowHistory.js"></script>

</body>
</html>