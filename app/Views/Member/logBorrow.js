$(document).ready(function () {
    loadMyRequests();
    loadMyBorrows();
    loadCatalog();
    loadUnreadCount();

    setInterval(function () {
        loadMyRequests();
        loadMyBorrows();
        loadCatalog();
        loadUnreadCount();
    }, 5000);
});

function showTab(tab) {
    $('#paneTrending, #paneRecommended, #paneAll').hide();
    if (tab === 'trending') $('#paneTrending').show();
    if (tab === 'recommended') $('#paneRecommended').show();
    if (tab === 'all') $('#paneAll').show();
}

function showError(msg) {
    $('#success_text').hide();
    $('#error_text').text(msg).fadeIn();
}

function showSuccess(msg) {
    $('#error_text').hide();
    $('#success_text').text(msg).fadeIn();
    setTimeout(function () { $('#success_text').fadeOut(); }, 3000);
}

function loadCatalog() {
    $.ajax({
        url: 'app/Controllers/borrowController.php',
        method: 'GET',
        data: { action: 'getCats' },
        dataType: 'json',
        success: function (data) {
            renderMatTable('#trendingTable', data.trending);
            renderMatTable('#recommendedTable', data.recommended);
            renderMatTable('#allTable', data.all);
        }
    });
}

function renderMatTable(selector, mats) {
    if (!mats || mats.length === 0) {
        $(selector + ' tbody').html('<tr><td colspan="6">No materials found</td></tr>');
        return;
    }

    var rows = '';
    mats.forEach(function (m) {
        rows += '<tr>' +
            '<td>' + m.Title + '</td>' +
            '<td>' + m.Author + '</td>' +
            '<td>' + m.TypeName + '</td>' +
            '<td>' + (m.Genre || 'N/A') + '</td>' +
            '<td>' + m.AvailableQuantity + '</td>' +
            '<td><button onclick="confirmBorrow(' + m.MaterialID + ', \'' + escapeAttr(m.Title) + '\')">Borrow</button></td>' +
        '</tr>';
    });

    $(selector + ' tbody').html(rows);
}

function confirmBorrow(matID, title) {
    if (!confirm('Request to borrow "' + title + '"?\n\nYour request will be reviewed by a librarian.')) return;
    submitBorrow(matID);
}

function submitBorrow(matID) {
    $.ajax({
        url: 'app/Controllers/borrowController.php',
        method: 'POST',
        data: { action: 'submitReq', materialID: matID },
        dataType: 'json',
        success: function (res) {
            if (res.success) {
                showSuccess(res.message);
                loadMyRequests();
                loadCatalog();
            } else {
                showError(res.message);
            }
        }
    });
}

function loadMyRequests() {
    $.ajax({
        url: 'app/Controllers/borrowController.php',
        method: 'GET',
        data: { action: 'getMyReq' },
        dataType: 'json',
        success: function (data) {
            if (!data || data.length === 0) {
                $('#myRequestsTable tbody').html('<tr><td colspan="5">No requests yet</td></tr>');
                return;
            }

            var rows = '';
            data.forEach(function (r) {
                var cancelBtn = '';
                if (r.Status === 'Pending') {
                    cancelBtn = '<button onclick="cancelRequest(' + r.RequestID + ')">Cancel</button>';
                }

                rows += '<tr>' +
                    '<td>' + r.Title + '</td>' +
                    '<td>' + r.TypeName + '</td>' +
                    '<td>' + r.RequestDate + '</td>' +
                    '<td>' + r.Status + '</td>' +
                    '<td>' + cancelBtn + '</td>' +
                '</tr>';
            });

            rows += '<tr><td colspan="5"><a href="app/Views/Member/myRequests.php">View All</a></td></tr>';
            $('#myRequestsTable tbody').html(rows);
        }
    });
}

function cancelRequest(reqID) {
    if (!confirm('Cancel this borrow request?')) return;

    $.ajax({
        url: 'app/Controllers/borrowController.php',
        method: 'POST',
        data: { action: 'cancelReq', requestID: reqID },
        dataType: 'json',
        success: function (res) {
            if (res.success) {
                showSuccess(res.message);
                loadMyRequests();
                loadCatalog();
            } else {
                showError(res.message);
            }
        }
    });
}

function loadMyBorrows() {
    $.ajax({
        url: 'app/Controllers/borrowController.php',
        method: 'GET',
        data: { action: 'getMyBorrows' },
        dataType: 'json',
        success: function (data) {
            if (!data || data.length === 0) {
                $('#myBorrowsTable tbody').html('<tr><td colspan="5">No active borrows</td></tr>');
                return;
            }

            var rows = '';
            data.forEach(function (b) {
                var statusStyle = b.Status === 'Overdue' ? 'color:red;font-weight:bold;' : '';
                rows += '<tr>' +
                    '<td>' + b.Title + '</td>' +
                    '<td>' + b.TypeName + '</td>' +
                    '<td>' + b.BorrowDate + '</td>' +
                    '<td>' + b.DueDate + '</td>' +
                    '<td style="' + statusStyle + '">' + b.Status + '</td>' +
                '</tr>';
            });

            $('#myBorrowsTable tbody').html(rows);
        }
    });
}

function escapeAttr(string) {
    return string.replace(/'/g, "\\'");
}

function toggleNotifications() {
    let panel = $('#notificationsPanel');
    if (panel.is(':hidden')) {
        loadNotifications();
        panel.fadeIn(200);
    } else {
        panel.fadeOut(200);
    }
}

function loadUnreadCount() {
    $.ajax({
        url: 'app/Controllers/notificationController.php',
        method: 'GET',
        data: { action: 'getUnreadCount' },
        dataType: 'json',
        success: function (data) {
            if (data.count > 0) {
                $('#unreadBadge').text('(' + data.count + ')').show();
            } else {
                $('#unreadBadge').hide();
            }
        }
    });
}

function loadNotifications() {
    $.ajax({
        url: 'app/Controllers/notificationController.php',
        method: 'GET',
        data: { action: 'getNotifications' },
        dataType: 'json',
        success: function (data) {
            if (!data || data.length === 0) {
                $('#notificationsTable tbody').html('<tr><td colspan="3">No notifications</td></tr>');
                return;
            }

            var rows = '';
            data.forEach(function (n) {
                var style = n.IsRead == 0 ? 'font-weight:bold;' : '';
                rows += '<tr style="' + style + '">' +
                    '<td>' + n.Message + '</td>' +
                    '<td>' + n.DateCreated + '</td>' +
                    '<td>' + (n.IsRead == 0 ? 'Unread' : 'Read') + '</td>' +
                '</tr>';
            });

            $('#notificationsTable tbody').html(rows);
        }
    });
}

function markAllRead() {
    $.ajax({
        url: 'app/Controllers/notificationController.php',
        method: 'POST',
        data: { action: 'markAllRead' },
        dataType: 'json',
        success: function () {
            loadNotifications();
            loadUnreadCount();
        }
    });
}