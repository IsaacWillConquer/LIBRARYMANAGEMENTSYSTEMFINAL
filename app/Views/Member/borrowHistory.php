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
    <title>Borrow History</title>
</head>
<body>

<header>
    <h1>Nyle's Library Management System</h1>
    <div>
        <a href="app/Views/Dashboards/memberDashboard.php">← Dashboard</a>
        <button class="btn-logout" onclick="logout()">Logout</button>
    </div>
</header>

<div class="welcome">Borrow History — <strong><?php echo htmlspecialchars($fname); ?></strong></div>

<main>
    <h2>My Borrow History</h2>
    <table id="historyTable">
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
            <tr>
                <td colspan="8">Loading...</td>
            </tr>
        </tbody>
    </table>

<footer id="foot">
    &copy; <?php echo date('Y'); ?> Library Management System
</footer>

</main>

<script src="app/Views/Auth/logAuth.js"></script>
<script src="app/Views/Member/logBorrowHistory.js"></script>


</body>
</html>