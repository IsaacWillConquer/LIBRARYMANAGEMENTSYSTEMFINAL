<?php
    require_once __DIR__ . "/../../../core/auth.php";
    mustBeStaff();

    if (isDefaultPassword()) {
        header('Location: /../Auth/changePass.php');
        exit();
    }

    $fname = $_SESSION['FullName'];
    $role  = $_SESSION['Role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <base href="/LIBRARYMANAGEMENTSYSTEMFINAL/">
    <script src="core/jq.js"></script>
    <link rel="stylesheet" href="app/Views/Circulation/manBorr.css">
    <title>Manage Borrows</title>
</head>
<body>

<div id="logoutText" style="display:flex;">Logging out...</div>

<header>
    <h1>Library Management System</h1>
    <div class="header-right">
        <div class="notif">
            <button class="btn btn-ghost" onclick="toggleNotifications()">Notifications</button>
            <span id="unreadBadge"></span>
        </div>
        <span><?php echo $role; ?> — <?php echo htmlspecialchars($fname); ?></span>
        <?php if ($role === 'Admin'): ?>
        <a class="btn btn-ghost" href="app/Views/Dashboards/adminDashboard.php">← Dashboard</a>
        <?php endif; ?>
        <button class="btn-logout" onclick="logout()">Logout</button>
    </div>
</header>

<div id="notificationsPanel">
    <div class="notif-head">Notifications <button class="btn-markread" onclick="markAllRead()">Mark all read</button></div>
    <div class="notif-body" id="notifBody">
        <div class="notif-row">Loading...</div></div>
</div>

<div class="subbar"><span>Manage Borrow Requests</span></div>

<div id="msg-ok"></div>
<div id="msg-err"></div>

<div class="modal-overlay" id="approveModal">
    <div class="modal-box">
        <h3>Approve Request</h3>
        <p id="approveModalTitle"></p>
        <label>Claim Start Date <input type="date" id="claimStartInput"></label>
        <p style="margin-top:8px;color:#888;font-size:.8rem;">Deadline: <strong id="deadlinePreview">—</strong> (3 days from start)</p>
        <div class="modal-btns">
            <button class="btn-approve" onclick="submitApprove()">Confirm</button>
            <button class="btn btn-ghost" style="color:#555;border-color:#ccc;" onclick="closeApproveModal()">Cancel</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="rejectModal">
    <div class="modal-box">
        <h3>Reject Request</h3>
        <p id="rejectModalTitle"></p>
        <label>Reason (optional) <textarea id="rejectRemarks" placeholder="e.g. Book unavailable, member has overdue items..."></textarea></label>
        <div class="modal-btns">
            <button class="btn-reject" onclick="submitReject()">Reject</button>
            <button class="btn btn-ghost" style="color:#555;border-color:#ccc;" onclick="closeRejectModal()">Cancel</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="returnModal">
    <div class="modal-box">
        <h3>Return Book</h3>
        <p id="returnModalTitle"></p>
        <div class="fine-preview" id="finePreview" style="display:none;"></div>
        <div class="modal-btns">
            <button class="btn-return" onclick="submitReturn()">Confirm Return</button>
            <button class="btn btn-ghost" style="color:#555;border-color:#ccc;" onclick="closeReturnModal()">Cancel</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="lostModal">
    <div class="modal-box">
        <h3 id="lostModalTitle">Mark as Lost/Damaged</h3>
        <p id="lostModalDesc"></p>
        <div class="modal-btns">
            <button class="btn-danger" onclick="submitLostDamaged()">Confirm</button>
            <button class="btn btn-ghost" style="color:#555;border-color:#ccc;" onclick="closeLostModal()">Cancel</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="claimPopup">
    <div class="modal-box">
        <h3 id="popupTitle"></h3>
        <p><strong>Author:</strong> <span id="popupAuthor"></span></p>
        <p><strong>Type:</strong> <span id="popupType"></span></p>
        <p><strong>Available Stock:</strong> <span id="popupStock"></span></p>
        <p><strong>Description:</strong> <span id="popupDesc"></span></p>
        <p><strong>Claim Deadline:</strong> <span id="popupDeadline"></span></p>
        <div class="modal-btns">
            <button class="btn-claim" id="popupClaimBtn">Mark as Claimed</button>
            <button class="btn btn-ghost" style="color:#555;border-color:#ccc;" onclick="closeClaimPopup()">Close</button>
        </div>
    </div>
</div>

<main>
    <div class="page-tabs">
        <button class="page-tab active" onclick="showPage('Requests', this)">Borrow Requests</button>
        <button class="page-tab" onclick="showPage('Claims', this)">Pending Claims</button>
        <button class="page-tab" onclick="showPage('Borrows', this)">Active Borrows</button>
        <button class="page-tab" onclick="showPage('Donations', this)">Donations</button>
    </div>

    <div class="page-pane active" id="paneRequests">
        <input class="tbl-search" id="pendingSearch" placeholder="Search member or title..." oninput="filterPending()">
        <table id="pendingTable">
            <thead>
                <tr>
                    <th>Member</th>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Author</th>
                    <th>Request Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="6">Loading...</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="page-pane" id="paneClaims">
        <input class="tbl-search" id="claimsSearch" placeholder="Search member or title..." oninput="filterClaims()">
        <table id="claimsTable">
            <thead>
                <tr>
                    <th>Member</th>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Approved Date</th>
                    <th>Claim Deadline</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="6">Loading...</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="page-pane" id="paneBorrows">
        <table id="activeBorrowsTable">
            <thead>
                <tr>
                    <th>Member</th>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Borrow Date</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="7">Loading...</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="page-pane" id="paneDonations">
        <table id="donationsTable">
            <thead>
                <tr>
                    <th>Member</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Genre</th>
                    <th>Condition</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="7">Loading...</td>
                </tr>
            </tbody>
        </table>
    </div>
</main>

<script src="app/Views/Auth/logAuth.js"></script>
<script src="app/Views/Circulation/logCirculation.js?v=6"></script>
</body>
</html>