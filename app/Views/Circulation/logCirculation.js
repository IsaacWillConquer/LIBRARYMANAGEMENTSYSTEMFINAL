var allPendingData = [];
var allClaimsData  = [];
var pendingReqID   = null;
var rejectReqID    = null;
var returnRecordID = null;
var lostRecordID   = null;
var lostCondition  = null;

$(document).ready(function () {
    getPendingReq();
    loadApprovedClaims();
    loadActBorrows();
    loadDonations();
    loadUnreadCount();

    $('#claimStartInput').on('change', updateDeadlinePreview);

    setInterval(function () {
        getPendingReq();
        loadApprovedClaims();
        loadActBorrows();
        loadDonations();
        loadUnreadCount();
    }, 8000);
});

function showPage(tab, btn) {
    $('.page-pane').removeClass('active');
    $('#pane' + tab).addClass('active');
    $('.page-tab').removeClass('active');
    if (btn) $(btn).addClass('active');
}

function showMsg(type, msg) {
    if (type === 'ok') {
        $('#msg-err').hide();
        $('#msg-ok').text(msg).fadeIn();
        setTimeout(function () { $('#msg-ok').fadeOut(); }, 3000);
    } else {
        $('#msg-ok').hide();
        $('#msg-err').text(msg).fadeIn();
    }
}

// ── pending requests ──────────────────────────────────────────────

function getPendingReq() {
    $.ajax({
        url: 'app/Controllers/circulationController.php',
        method: 'GET', data: { action: 'getPendingReq' }, dataType: 'json',
        success: function (data) { allPendingData = data || []; renderPending(allPendingData); }
    });
}

function renderPending(data) {
    if (!data || data.length === 0) {
        $('#pendingTable tbody').html('<tr><td colspan="6" style="color:#aaa;text-align:center;">No pending requests</td></tr>');
        return;
    }
    var rows = '';
    data.forEach(function (r) {
        rows += '<tr>' +
            '<td>' + r.MemberName + '</td><td>' + r.Title + '</td><td>' + r.TypeName +
            '</td><td>' + r.Author + '</td><td>' + r.RequestDate + '</td><td>' +
            '<button class="btn-approve" onclick="openApproveModal(' + r.RequestID + ',\'' + escapeAttr(r.Title) + '\',\'' + escapeAttr(r.MemberName) + '\')">Approve</button> ' +
            '<button class="btn-reject" onclick="openRejectModal(' + r.RequestID + ',\'' + escapeAttr(r.Title) + '\',\'' + escapeAttr(r.MemberName) + '\')">Reject</button>' +
            '</td></tr>';
    });
    $('#pendingTable tbody').html(rows);
}

function filterPending() {
    var q = $('#pendingSearch').val().toLowerCase();
    renderPending(!q ? allPendingData : allPendingData.filter(function (r) {
        return r.MemberName.toLowerCase().includes(q) || r.Title.toLowerCase().includes(q);
    }));
}

// ── approve modal ─────────────────────────────────────────────────

function openApproveModal(requestID, title, member) {
    pendingReqID = requestID;
    $('#approveModalTitle').text(member + ' — ' + title);
    var today = new Date().toISOString().split('T')[0];
    $('#claimStartInput').val(today);
    updateDeadlinePreview();
    $('#approveModal').fadeIn(150);
}
function closeApproveModal() { $('#approveModal').fadeOut(150); pendingReqID = null; }

function updateDeadlinePreview() {
    var start = $('#claimStartInput').val();
    if (!start) { $('#deadlinePreview').text('—'); return; }
    var parts = start.split('-');
    var d = new Date(parts[0], parts[1] - 1, parseInt(parts[2]) + 3);
    $('#deadlinePreview').text(
        d.getFullYear() + '-' +
        String(d.getMonth() + 1).padStart(2, '0') + '-' +
        String(d.getDate()).padStart(2, '0')
    );
}

function submitApprove() {
    var start = $('#claimStartInput').val();
    if (!start) { alert('Pick a start date'); return; }
    var parts = start.split('-');
    var d = new Date(parts[0], parts[1] - 1, parseInt(parts[2]) + 3);
    var deadline = d.getFullYear() + '-' +
        String(d.getMonth() + 1).padStart(2, '0') + '-' +
        String(d.getDate()).padStart(2, '0');

    $.ajax({
        url: 'app/Controllers/circulationController.php',
        method: 'POST',
        data: { action: 'approveReq', requestID: pendingReqID, claimDeadline: deadline },
        dataType: 'json',
        success: function (res) {
            closeApproveModal();
            if (res.success) { showMsg('ok', res.message); getPendingReq(); loadApprovedClaims(); }
            else showMsg('err', res.message);
        }
    });
}

// ── reject modal ──────────────────────────────────────────────────

function openRejectModal(requestID, title, member) {
    rejectReqID = requestID;
    $('#rejectModalTitle').text(member + ' — ' + title);
    $('#rejectRemarks').val('');
    $('#rejectModal').fadeIn(150);
}
function closeRejectModal() { $('#rejectModal').fadeOut(150); rejectReqID = null; }

function submitReject() {
    $.ajax({
        url: 'app/Controllers/circulationController.php',
        method: 'POST',
        data: { action: 'rejectReq', requestID: rejectReqID, remarks: $('#rejectRemarks').val().trim() },
        dataType: 'json',
        success: function (res) {
            closeRejectModal();
            if (res.success) { showMsg('ok', res.message); getPendingReq(); }
            else showMsg('err', res.message);
        }
    });
}

// ── approved claims ───────────────────────────────────────────────

function loadApprovedClaims() {
    $.ajax({
        url: 'app/Controllers/circulationController.php',
        method: 'GET', data: { action: 'getApprovedClaims' }, dataType: 'json',
        success: function (data) { allClaimsData = data || []; renderClaims(allClaimsData); }
    });
}

function renderClaims(data) {
    if (!data || data.length === 0) {
        $('#claimsTable tbody').html('<tr><td colspan="6" style="color:#aaa;text-align:center;">No pending claims</td></tr>');
        return;
    }
    var today = new Date().toISOString().split('T')[0];
    var rows  = '';
    data.forEach(function (c) {
        var deadStyle = c.ClaimDeadline && c.ClaimDeadline < today ? 'color:red;font-weight:bold;' : '';
        rows += '<tr style="cursor:pointer;" onclick="openClaimPopup(' +
            c.RequestID + ',\'' + escapeAttr(c.Title) + '\',\'' + escapeAttr(c.Author) + '\',\'' +
            c.TypeName + '\',' + c.AvailableQuantity + ',\'' + escapeAttr(c.Description || '') + '\',\'' + (c.ClaimDeadline || '') + '\')">' +
            '<td>' + c.MemberName + '</td><td>' + c.Title + '</td><td>' + c.TypeName +
            '</td><td>' + c.ProcessedDate + '</td>' +
            '<td style="' + deadStyle + '">' + (c.ClaimDeadline || 'Not set') + '</td>' +
            '<td><button class="btn-claim" onclick="event.stopPropagation();markClaimed(' + c.RequestID + ')">Mark Claimed</button></td>' +
            '</tr>';
    });
    $('#claimsTable tbody').html(rows);
}

function filterClaims() {
    var q = $('#claimsSearch').val().toLowerCase();
    renderClaims(!q ? allClaimsData : allClaimsData.filter(function (c) {
        return c.MemberName.toLowerCase().includes(q) || c.Title.toLowerCase().includes(q);
    }));
}

function openClaimPopup(requestID, title, author, type, stock, desc, deadline) {
    $('#popupTitle').text(title); $('#popupAuthor').text(author); $('#popupType').text(type);
    $('#popupStock').text(stock); $('#popupDesc').text(desc || 'No description');
    $('#popupDeadline').text(deadline || 'Not set');
    $('#popupClaimBtn').off('click').on('click', function () { closeClaimPopup(); markClaimed(requestID); });
    $('#claimPopup').fadeIn(150);
}
function closeClaimPopup() { $('#claimPopup').fadeOut(150); }

function markClaimed(requestID) {
    if (!confirm('Mark this book as physically claimed by the member?')) return;
    $.ajax({
        url: 'app/Controllers/circulationController.php',
        method: 'POST', data: { action: 'markClaimed', requestID: requestID }, dataType: 'json',
        success: function (res) {
            if (res.success) { showMsg('ok', res.message); loadApprovedClaims(); loadActBorrows(); }
            else showMsg('err', res.message);
        }
    });
}

// ── active borrows ────────────────────────────────────────────────

function loadActBorrows() {
    $.ajax({
        url: 'app/Controllers/circulationController.php',
        method: 'GET', data: { action: 'getActiveBorrows' }, dataType: 'json',
        success: function (data) {
            if (!data || data.length === 0) {
                $('#activeBorrowsTable tbody').html('<tr><td colspan="7" style="color:#aaa;text-align:center;">No active borrows</td></tr>');
                return;
            }
            var rows = '';
            data.forEach(function (b) {
                var overdueStyle = b.Status === 'Overdue' ? 'color:red;font-weight:bold;' : '';
                var daysOverdue  = '';
                if (b.Status === 'Overdue') {
                    var diff = Math.floor((new Date() - new Date(b.DueDate)) / 86400000);
                    daysOverdue = ' (' + diff + 'd)';
                }
                rows += '<tr>' +
                    '<td>' + b.MemberName + '</td><td>' + b.Title + '</td><td>' + b.TypeName +
                    '</td><td>' + (b.BorrowDate || '—') + '</td><td>' + b.DueDate +
                    '</td><td style="' + overdueStyle + '">' + b.Status + daysOverdue + '</td><td>' +
                    '<button class="btn-return" onclick="openReturnModal(' + b.RecordID + ',\'' + escapeAttr(b.Title) + '\',\'' + b.DueDate + '\',\'' + b.Status + '\')">Return</button> ' +
                    '<button class="btn-warn" onclick="openLostModal(' + b.RecordID + ',\'' + escapeAttr(b.Title) + '\',\'Lost\')">Lost</button> ' +
                    '<button class="btn-danger" onclick="openLostModal(' + b.RecordID + ',\'' + escapeAttr(b.Title) + '\',\'Damaged\')">Damaged</button>' +
                    '</td></tr>';
            });
            $('#activeBorrowsTable tbody').html(rows);
        }
    });
}

// ── return modal with fine preview ────────────────────────────────

function openReturnModal(recordID, title, dueDate, status) {
    returnRecordID = recordID;
    $('#returnModalTitle').text(title);
    var $fp = $('#finePreview');
    if (status === 'Overdue') {
        var diff = Math.floor((new Date() - new Date(dueDate)) / 86400000);
        var fine = diff * 5; // ₱5/day default, matches settings
        $fp.text('Overdue by ' + diff + ' day(s) × ₱5/day = ₱' + fine.toFixed(2) + ' fine').show();
    } else {
        $fp.hide();
    }
    $('#returnModal').fadeIn(150);
}
function closeReturnModal() { $('#returnModal').fadeOut(150); returnRecordID = null; }

function submitReturn() {
    $.ajax({
        url: 'app/Controllers/circulationController.php',
        method: 'POST', data: { action: 'returnBook', recordID: returnRecordID }, dataType: 'json',
        success: function (res) {
            closeReturnModal();
            if (res.success) { showMsg('ok', res.message); loadActBorrows(); getPendingReq(); }
            else showMsg('err', res.message);
        }
    });
}

// ── lost/damaged modal ────────────────────────────────────────────

function openLostModal(recordID, title, condition) {
    lostRecordID  = recordID;
    lostCondition = condition;
    $('#lostModalTitle').text('Mark as ' + condition);
    $('#lostModalDesc').text('Mark "' + title + '" as ' + condition.toLowerCase() + '? A replacement fine will be applied and the book will be removed from active borrows.');
    $('#lostModal').fadeIn(150);
}
function closeLostModal() { $('#lostModal').fadeOut(150); lostRecordID = null; lostCondition = null; }

function submitLostDamaged() {
    $.ajax({
        url: 'app/Controllers/circulationController.php',
        method: 'POST',
        data: { action: 'markLostDamaged', recordID: lostRecordID, condition: lostCondition },
        dataType: 'json',
        success: function (res) {
            closeLostModal();
            if (res.success) { showMsg('ok', res.message); loadActBorrows(); }
            else showMsg('err', res.message);
        }
    });
}

// ── donations ─────────────────────────────────────────────────────

function loadDonations() {
    $.ajax({
        url: 'app/Controllers/circulationController.php',
        method: 'GET', data: { action: 'getPendingDonations' }, dataType: 'json',
        success: function (data) {
            if (!data || data.length === 0) {
                $('#donationsTable tbody').html('<tr><td colspan="7" style="color:#aaa;text-align:center;">No pending donations</td></tr>');
                return;
            }
            var rows = '';
            data.forEach(function (d) {
                rows += '<tr><td>' + d.MemberName + '</td><td>' + d.Title + '</td><td>' + d.Author +
                    '</td><td>' + (d.Genre || '—') + '</td><td>' + d.BookCondition +
                    '</td><td>' + d.DateSubmitted + '</td><td>' +
                    '<button class="btn-approve" onclick="reviewDonation(' + d.DonationID + ',\'Accepted\')">Accept</button> ' +
                    '<button class="btn-reject" onclick="reviewDonation(' + d.DonationID + ',\'Rejected\')">Reject</button>' +
                    '</td></tr>';
            });
            $('#donationsTable tbody').html(rows);
        }
    });
}

function reviewDonation(donationID, status) {
    if (!confirm(status + ' this donation?')) return;
    $.ajax({
        url: 'app/Controllers/circulationController.php',
        method: 'POST', data: { action: 'reviewDonation', donationID: donationID, status: status }, dataType: 'json',
        success: function (res) {
            if (res.success) { showMsg('ok', res.message); loadDonations(); }
            else showMsg('err', res.message);
        }
    });
}

// ── notifications ─────────────────────────────────────────────────

function loadUnreadCount() {
    $.ajax({
        url: 'app/Controllers/notificationController.php',
        method: 'GET', data: { action: 'getUnreadCount' }, dataType: 'json',
        success: function (data) {
            var $b = $('#unreadBadge');
            if (data.count > 0) $b.text(data.count).css('display', 'flex'); else $b.hide();
        }
    });
}

function toggleNotifications() {
    var $p = $('#notificationsPanel');
    if ($p.is(':hidden')) { loadNotifications(); $p.fadeIn(200); } else $p.fadeOut(200);
}

function loadNotifications() {
    $.ajax({
        url: 'app/Controllers/notificationController.php',
        method: 'GET', data: { action: 'getNotifications' }, dataType: 'json',
        success: function (data) {
            var $body = $('#notifBody');
            if (!data || data.length === 0) { $body.html('<div class="notif-row">No notifications</div>'); return; }
            var html = '';
            data.forEach(function (n) {
                var cls = n.IsRead == 0 ? 'notif-row unread' : 'notif-row';
                html += '<div class="' + cls + '">' + n.Message + '<div class="notif-date">' + n.DateCreated + '</div></div>';
            });
            $body.html(html);
        }
    });
}

function markAllRead() {
    $.ajax({
        url: 'app/Controllers/notificationController.php',
        method: 'POST', data: { action: 'markAllRead' }, dataType: 'json',
        success: function () { loadNotifications(); loadUnreadCount(); }
    });
}

function escapeAttr(str) { return str.replace(/'/g, "\\'").replace(/"/g, '&quot;'); }