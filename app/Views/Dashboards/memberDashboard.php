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
    <style>
    
        .modal-overlay {
            display: none; position: fixed; top: 0; left: 0;
            width: 100%; height: 100%; background: rgba(0,0,0,.5); z-index: 200;
        }
        .modal-box {
            background: #fff; border-radius: 5px;
            width: 420px; margin: 80px auto; padding: 20px;
        }
        .modal-box h3 { margin: 0 0 14px; color: #1a2744; font-size: .95rem; }
        .modal-box label { display: block; font-size: .8rem; font-weight: bold; margin-top: 10px; color: #555; }
        .modal-box input, .modal-box select, .modal-box textarea {
            width: 100%; padding: 6px 8px; font-size: .83rem;
            border: 1px solid #ccc; border-radius: 3px; box-sizing: border-box; margin-top: 3px;
            font-family: Arial, sans-serif;
        }
        .modal-box textarea { height: 70px; resize: vertical; }
        .modal-btns { margin-top: 16px; display: flex; gap: 8px; justify-content: flex-end; }
        .btn-primary {
            background: #1a2744; color: #fff; border: none; border-radius: 3px;
            padding: 6px 14px; font-size: .82rem; cursor: pointer; font-family: Arial, sans-serif;
        }
        .btn-primary:hover { background: #243358; }
        .btn-ghost {
            background: none; border: 1px solid #ccc; border-radius: 3px;
            padding: 6px 14px; font-size: .82rem; cursor: pointer; font-family: Arial, sans-serif;
        }

        /* ebook reader modal - fullscreen style */
        #ebookReaderModal {
            display: none; position: fixed; top: 0; left: 0;
            width: 100%; height: 100%; background: rgba(0,0,0,.85); z-index: 300;
        }
        .reader-box {
            background: #fff; width: 90%; max-width: 900px;
            height: 90vh; margin: 3vh auto; border-radius: 5px;
            display: flex; flex-direction: column; overflow: hidden;
        }
        .reader-header {
            background: #1a2744; color: #fff; padding: 10px 16px;
            display: flex; align-items: flex-start; justify-content: space-between; flex-shrink: 0;
        }
        .reader-header-info { flex: 1; }
        .reader-header h3 { margin: 0; font-size: .95rem; }
        .reader-header p  { margin: 2px 0 0; font-size: .78rem; color: #aac; }
        .reader-close {
            background: none; border: none; color: #fff; font-size: 1.2rem;
            cursor: pointer; padding: 0 4px; line-height: 1;
        }
        #readerFrame { flex: 1; width: 100%; border: none; }

        /* currently reading cards */
        #currentlyReadingSection { margin-bottom: 20px; }
        #currentlyReadingCards { display: flex; gap: 10px; flex-wrap: wrap; }
        .reading-card {
            background: #fff; border: 1px solid #e0e4ec; border-radius: 4px;
            padding: 10px 14px; cursor: pointer; width: 200px;
            transition: box-shadow .15s;
        }
        .reading-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,.1); }
        .reading-title { font-size: .85rem; font-weight: bold; color: #1a2744; margin-bottom: 4px; }
        .reading-meta  { font-size: .75rem; color: #888; }

        /* claim popup */
        .claim-popup { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,.5); z-index: 200; }
        .claim-popup-box { background: #fff; padding: 20px; border-radius: 5px; width: 400px; margin: 100px auto; }
        .claim-popup-box h3 { margin: 0 0 12px; color: #1a2744; font-size: .95rem; }
        .claim-popup-box p  { font-size: .84rem; margin: 6px 0; color: #444; }
    </style>
</head>
<body>

<header>
    <h1>Library Management System</h1>
    <div>
        <div class="notif-wrap" style="display:inline-block;">
            <button class="nav-btn" onclick="toggleNotifications()">Notifications</button>
            <span id="unreadBadge"></span>
        </div>
        <a href="app/Views/Member/borrowHistory.php">My History</a>
        <button class="nav-btn" onclick="openDonateModal()">Donate a Book</button>
        <button class="btn-logout" onclick="logout()">Logout</button>
    </div>
</header>

<div class="welcome">Welcome, <strong><?php echo htmlspecialchars($fname); ?></strong></div>

<div id="error_text"></div>
<div id="success_text"></div>

<!-- notifications panel -->
<div id="notificationsPanel">
    <div class="notif-head">
        Notifications
        <button class="btn-markread" onclick="markAllRead()">Mark all read</button>
    </div>
    <div class="notif-body" id="notifBody">
        <div class="notif-row">Loading...</div>
    </div>
</div>

<!-- ebook reader modal -->
<div id="ebookReaderModal">
    <div class="reader-box">
        <div class="reader-header">
            <div class="reader-header-info">
                <h3 id="readerTitle"></h3>
                <p id="readerAuthor"></p>
                <p id="readerDesc" style="margin-top:4px;font-size:.75rem;color:#8ab;"></p>
            </div>
            <button class="reader-close" onclick="closeEbookReader()">✕</button>
        </div>
        <iframe id="readerFrame" src=""></iframe>
    </div>
</div>

<!-- claim detail popup -->
<div class="claim-popup" id="claimDetailPopup">
    <div class="claim-popup-box">
        <h3 id="cdTitle"></h3>
        <p><strong>Author:</strong> <span id="cdAuthor"></span></p>
        <p><strong>Type:</strong> <span id="cdType"></span></p>
        <p><strong>Remaining Stock:</strong> <span id="cdStock"></span></p>
        <p><strong>Description:</strong> <span id="cdDesc"></span></p>
        <p><strong>Claim Deadline:</strong> <span id="cdDeadline"></span></p>
        <div class="modal-btns"><button class="btn-ghost" onclick="closeCdPopup()">Close</button></div>
    </div>
</div>

<!-- donate modal -->
<div class="modal-overlay" id="donateModal">
    <div class="modal-box">
        <h3>Donate a Book</h3>
        <form id="donateForm" onsubmit="return false;">
            <label>Title *<input type="text" id="donateTitle" placeholder="Book title"></label>
            <label>Author *<input type="text" id="donateAuthor" placeholder="Author name"></label>
            <label>Genre<input type="text" id="donateGenre" placeholder="e.g. Fiction, Technology"></label>
            <label>Condition
                <select id="donateCond">
                    <option value="New">New</option>
                    <option value="Good" selected>Good</option>
                    <option value="Fair">Fair</option>
                    <option value="Poor">Poor</option>
                </select>
            </label>
            <label>Description / Notes<textarea id="donateDesc" placeholder="Any notes about the book..."></textarea></label>
        </form>
        <div class="modal-btns">
            <button class="btn-primary" onclick="submitDonation()">Submit Donation</button>
            <button class="btn-ghost" onclick="closeDonateModal()">Cancel</button>
        </div>
    </div>
</div>

<main>

    <!-- currently reading - hidden until data loads -->
    <div id="currentlyReadingSection" style="display:none;">
        <h2>Continue Reading</h2>
        <div id="currentlyReadingCards"></div>
    </div>

    <h2>My Active Borrows</h2>
    <table id="myBorrowsTable">
        <thead>
            <tr><th>Title</th><th>Type</th><th>Borrow Date</th><th>Due Date</th><th>Status</th></tr>
        </thead>
        <tbody><tr><td colspan="5">Loading...</td></tr></tbody>
    </table>

    <h2>Books to Claim at Library</h2>
    <input type="text" id="claimSearch" placeholder="Search by title..." oninput="filterPendingClaims()" style="margin-bottom:8px;padding:5px 8px;border:1px solid #ccc;border-radius:3px;font-size:.83rem;width:240px;">
    <table id="pendingClaimsTable">
        <thead>
            <tr><th>Title</th><th>Type</th><th>Approved Date</th><th>Claim Deadline</th><th>Action</th></tr>
        </thead>
        <tbody><tr><td colspan="5">Loading...</td></tr></tbody>
    </table>

    <h2>My Requests</h2>
    <table id="myRequestsTable">
        <thead>
            <tr><th>Title</th><th>Type</th><th>Request Date</th><th>Status</th><th>Action</th></tr>
        </thead>
        <tbody><tr><td colspan="5">Loading...</td></tr></tbody>
    </table>

    <h2>Browse Materials</h2>
    <div style="display:flex;gap:8px;margin-bottom:10px;align-items:center;">
        <input type="text" id="matSearch" placeholder="Search by title, author, genre..." style="padding:5px 8px;border:1px solid #ccc;border-radius:3px;font-size:.83rem;width:280px;">
        <select id="typeFilter" style="padding:5px 8px;border:1px solid #ccc;border-radius:3px;font-size:.83rem;">
            <option value="">All Types</option>
            <option value="Book">Book</option>
            <option value="EBook">EBook</option>
            <option value="Journal">Journal</option>
        </select>
    </div>

    <div class="tab-bar">
        <button class="tab-btn active" onclick="showTab('trending', this)">Trending</button>
        <button class="tab-btn" onclick="showTab('recommended', this)">Recommended</button>
        <button class="tab-btn" onclick="showTab('all', this)">All Available</button>
    </div>

    <div id="paneTrending">
        <table id="trendingTable">
            <thead><tr><th>Title</th><th>Author</th><th>Type</th><th>Genre</th><th>Available</th><th>Action</th></tr></thead>
            <tbody><tr><td colspan="6">Loading...</td></tr></tbody>
        </table>
    </div>

    <div id="paneRecommended" style="display:none;">
        <table id="recommendedTable">
            <thead><tr><th>Title</th><th>Author</th><th>Type</th><th>Genre</th><th>Available</th><th>Action</th></tr></thead>
            <tbody><tr><td colspan="6">Loading...</td></tr></tbody>
        </table>
    </div>

    <div id="paneAll" style="display:none;">
        <table id="allTable">
            <thead><tr><th>Title</th><th>Author</th><th>Type</th><th>Genre</th><th>Available</th><th>Action</th></tr></thead>
            <tbody><tr><td colspan="6">Loading...</td></tr></tbody>
        </table>
    </div>

</main>

<script src="app/Views/Auth/logAuth.js"></script>
<script src="app/Views/Member/logBorrow.js?v=5"></script>

</body>
</html>