<?php
session_start();

if (isset($_SESSION['StaffID'])) {
    switch ($_SESSION['Role']) {
        case 'Admin':
            header('Location: app/Views/Dashboards/adminDashboard.php'); exit();
        case 'CirculationLibrarian':
            header('Location: app/Views/Circulation/manageBorrows.php'); exit();
        case 'DataAnalyst':
            header('Location: app/Views/Dashboards/analystDashboard.php'); exit();
    }
}
if (isset($_SESSION['MemberID'])) {
    header('Location: app/Views/Dashboards/memberDashboard.php'); exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/LIBRARYMANAGEMENTSYSTEMFINAL/">
    <script src="core/jq.js"></script>
    <link rel="stylesheet" href="app/Views/CSS/login.css">
    <title>Library — Login</title>
</head>
<body>

<header>
    <h1>Library Management System</h1>
</header>

<div class="page-body">

    <div class="info-side">
        <h2>Welcome to Library</h2>
        <p class="tagline">Your one-stop library portal for borrowing books, reading ebooks, and managing your account.</p>

        <div class="info-card">
            <h4>What You Can Do</h4>
            <ul>
                <li>Browse and borrow physical books & journals</li>
                <li>Read ebooks directly in your browser</li>
                <li>Track your active borrows and due dates</li>
                <li>View your borrow history and fines</li>
                <li>Donate books to the library</li>
                <li>Get real-time notifications</li>
            </ul>
        </div>

        <div class="info-card green">
            <h4>Borrow Policy</h4>
            <ul>
                <li>Borrow period: <strong>14 days</strong></li>
                <li>Overdue fine: <strong>₱5 per day</strong></li>
                <li>Claim window: <strong>3 days</strong> after approval</li>
                <li>Max borrows at a time: <strong>3 items</strong></li>
            </ul>
        </div>

        <div class="info-card teal">
            <h4>Library Hours</h4>
            <div class="hours-row"><span>Monday – Friday</span><span>8:00 AM – 6:00 PM</span></div>
            <div class="hours-row"><span>Saturday</span><span>9:00 AM – 4:00 PM</span></div>
            <div class="hours-row"><span>Sunday</span><span>Closed</span></div>
        </div>

        <div class="info-card orange">
            <h4>First Time?</h4>
            <p>Contact the librarian to register your account. Your default password will be <strong>Library123</strong> — you'll be asked to change it on first login.</p>
        </div>
    </div>

    <div class="login-side">
        <div class="login-box">
            <h3>Sign In to Your Account</h3>

            <label>Email Address</label>
            <input type="email" id="email" placeholder="your@email.com">

            <label>Password</label>
            <input type="password" id="password" placeholder="Password">

            <button id="loginBtn">Login</button>

            <div id="error_text"></div>

            <p class="login-note">
                Forgot your password? Contact the librarian to reset it.<br>
                Staff and members use the same login form.
            </p>
        </div>
    </div>

</div>

<footer id="footers">
    &copy; <?php echo date('Y'); ?> Library Management System
</footer>

<script src="app/Views/Auth/logAuth.js"></script>
</body>
</html>