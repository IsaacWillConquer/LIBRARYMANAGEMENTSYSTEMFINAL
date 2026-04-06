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

    let matSearchTimer;
    $('#matSearch').on('keyup', function () {
        clearTimeout(matSearchTimer);
        let q = $(this).val().trim();
        matSearchTimer = setTimeout(function () {
            doMatSearch(q);
        }, 300);
});
});

function showTab(tab, btn) {
    $('#paneTrending, #paneRecommended, #paneAll').hide();
    if (tab === 'trending') $('#paneTrending').show();
    if (tab === 'recommended') $('#paneRecommended').show();
    if (tab === 'all')   $('#paneAll').show();
    $('.tab-btn').removeClass('active');
    if (btn) $(btn).addClass('active');
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
        $(selector + ' tbody').html('<tr><td colspan="6" style="color:#aaa;text-align:center;">No materials found</td></tr>');
        return;
    }
    var rows = '';
    mats.forEach(function (m) {
        var isEbook = m.TypeName === 'EBook';
        var availCell = isEbook ? '<em style="color:#aaa;">Digital</em>' : m.AvailableQuantity;
        var btn = isEbook
            ? '<button class="action-btn" onclick="confirmBorrow(' + m.MaterialID + ', \'' + escapeAttr(m.Title) + '\', true)">Access</button>'
            : '<button class="action-btn" onclick="confirmBorrow(' + m.MaterialID + ', \'' + escapeAttr(m.Title) + '\', false)">Borrow</button>';

        rows += '<tr>' +
            '<td>' + m.Title + '</td>' +
            '<td>' + m.Author + '</td>' +
            '<td>' + m.TypeName + '</td>' +
            '<td>' + (m.Genre || 'N/A') + '</td>' +
            '<td>' + availCell + '</td>' +
            '<td>' + btn + '</td>' +
        '</tr>';
    });
    $(selector + ' tbody').html(rows);
}

function confirmBorrow(matID, title, isEbook) {
    var msg = isEbook
        ? 'Access "' + title + '"? This will be logged.'
        : 'Request to borrow "' + title + '"?';
    if (!confirm(msg)) return;
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
                $('#myRequestsTable tbody').html('<tr><td colspan="5" style="color:#aaa;text-align:center;">No requests yet</td></tr>');
                return;
            }
            var rows = '';
            data.forEach(function (r) {
                var cancelBtn = r.Status === 'Pending'
                    ? '<button class="cancel-btn" onclick="cancelRequest(' + r.RequestID + ')">Cancel</button>'
                    : '';
                var pill = '<span class="status-pill s-' + r.Status.toLowerCase() + '">' + r.Status + '</span>';
                rows += '<tr>' +
                    '<td>' + r.Title + '</td>' +
                    '<td>' + r.TypeName + '</td>' +
                    '<td>' + r.RequestDate + '</td>' +
                    '<td>' + pill + '</td>' +
                    '<td>' + cancelBtn + '</td>' +
                '</tr>';
            });
            $('#myRequestsTable tbody').html(rows);
        }
    });
}

function cancelRequest(reqID) {
    if (!confirm('Cancel this request?')) return;
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
                $('#myBorrowsTable tbody').html('<tr><td colspan="5" style="color:#aaa;text-align:center;">No active borrows</td></tr>');
                return;
            }
            var rows = '';
            data.forEach(function (b) {
                var pill = '<span class="status-pill s-' + b.Status.toLowerCase() + '">' + b.Status + '</span>';
                rows += '<tr>' +
                    '<td>' + b.Title + '</td>' +
                    '<td>' + b.TypeName + '</td>' +
                    '<td>' + b.BorrowDate + '</td>' +
                    '<td>' + b.DueDate + '</td>' +
                    '<td>' + pill + '</td>' +
                '</tr>';
            });
            $('#myBorrowsTable tbody').html(rows);
        }
    });
}

function toggleNotifications() {
    var $p = $('#notificationsPanel');
    if ($p.is(':hidden')) {
        loadNotifications();
        $p.fadeIn(200);
    } else {
        $p.fadeOut(200);
    }
}

function loadUnreadCount() {
    $.ajax({
        url: 'app/Controllers/notificationController.php',
        method: 'GET',
        data: { action: 'getUnreadCount' },
        dataType: 'json',
        success: function (data) {
            var $badge = $('#unreadBadge');
            if (data.count > 0) {
                $badge.text(data.count).css('display', 'flex');
            } else {
                $badge.hide();
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
            var $body = $('#notifBody');
            if (!data || data.length === 0) {
                $body.html('<div class="notif-row">No notifications</div>');
                return;
            }
            var html = '';
            data.forEach(function (n) {
                var cls = n.IsRead == 0 ? 'notif-row unread' : 'notif-row';
                html += '<div class="' + cls + '">' +
                    n.Message +
                    '<div class="notif-date">' + n.DateCreated + '</div>' +
                '</div>';
            });
            $body.html(html);
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

function escapeAttr(str) {
    return str.replace(/'/g, "\\'");
}

function doMatSearch(q) {
    if (q === '') {
        loadCatalog();
        return;
    }
    $.ajax({
        url: 'app/Controllers/borrowController.php',
        method: 'GET',
        data: { action: 'search', q: q },
        dataType: 'json',
        success: function (data) {
            // show results in all tab, switch to it
            renderMatTable('#allTable', data);
            showTab('all', $('.tab-btn').last()[0]);
        }
    });
}