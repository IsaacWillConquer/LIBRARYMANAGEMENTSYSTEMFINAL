var allPendingClaims = [];

$(document).ready(function () {
    loadMyRequests();
    loadMyBorrows();
    loadPendingClaims();
    loadCurrentlyReading();
    loadCatalog();
    loadUnreadCount();

    setInterval(function () {
        loadMyRequests();
        loadMyBorrows();
        loadPendingClaims();
        loadCurrentlyReading();
        loadCatalog();
        loadUnreadCount();
    }, 10000);

    let matSearchTimer;
    $('#matSearch').on('keyup', function () {
        clearTimeout(matSearchTimer);
        var q = $(this).val().trim();
        matSearchTimer = setTimeout(function () { doMatSearch(q); }, 300);
    });

    $('#typeFilter').on('change', function () {
        doMatSearch($('#matSearch').val().trim());
    });
});

function showTab(tab, btn) {
    $('#paneTrending, #paneRecommended, #paneAll').hide();
    if (tab === 'trending')    $('#paneTrending').show();
    if (tab === 'recommended') $('#paneRecommended').show();
    if (tab === 'all')         $('#paneAll').show();
    $('.tab-btn').removeClass('active');
    if (btn) $(btn).addClass('active');
}

function showError(msg) { $('#success_text').hide(); $('#error_text').text(msg).fadeIn(); }
function showSuccess(msg) {
    $('#error_text').hide(); $('#success_text').text(msg).fadeIn();
    setTimeout(function () { $('#success_text').fadeOut(); }, 3000);
}

// ── catalog ───────────────────────────────────────────────────────

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
        var isEbook   = m.TypeName === 'EBook';
        var availCell = isEbook ? '<em style="color:#aaa;">Digital</em>' : m.AvailableQuantity;
        var btn       = isEbook
            ? '<button class="action-btn" onclick="openEbookReader(' + m.MaterialID + ', \'' + escapeAttr(m.Title) + '\')">Read</button>'
            : '<button class="action-btn" onclick="confirmBorrow(' + m.MaterialID + ', \'' + escapeAttr(m.Title) + '\')">Borrow</button>';

        rows += '<tr><td>' + m.Title + '</td><td>' + m.Author + '</td><td>' + m.TypeName +
            '</td><td>' + (m.Genre || 'N/A') + '</td><td>' + availCell + '</td><td>' + btn + '</td></tr>';
    });
    $(selector + ' tbody').html(rows);
}

function confirmBorrow(matID, title) {
    if (!confirm('Request to borrow "' + title + '"?')) return;
    $.ajax({
        url: 'app/Controllers/borrowController.php',
        method: 'POST',
        data: { action: 'submitReq', materialID: matID },
        dataType: 'json',
        success: function (res) {
            if (res.success) { showSuccess(res.message); loadMyRequests(); loadCatalog(); }
            else showError(res.message);
        }
    });
}

// ── ebook reader ──────────────────────────────────────────────────

function openEbookReader(matID, title) {
    $.ajax({
        url: 'app/Controllers/borrowController.php',
        method: 'POST',
        data: { action: 'accessEbook', materialID: matID },
        dataType: 'json',
        success: function (res) {
            if (!res.success) { showError(res.message); return; }
            var mat = res.material;
            $('#readerTitle').text(mat.Title);
            $('#readerAuthor').text('by ' + mat.Author + (mat.Genre ? ' · ' + mat.Genre : ''));
            $('#readerDesc').text(mat.Description || '');
            $('#readerFrame').attr('src', 'assets/sample.pdf');
            $('#ebookReaderModal').fadeIn(150);
            loadCurrentlyReading();
        }
    });
}

function closeEbookReader() {
    $('#ebookReaderModal').fadeOut(150);
    $('#readerFrame').attr('src', '');
}

// ── currently reading ─────────────────────────────────────────────

function loadCurrentlyReading() {
    $.ajax({
        url: 'app/Controllers/borrowController.php',
        method: 'GET',
        data: { action: 'getCurrentlyReading' },
        dataType: 'json',
        success: function (data) {
            if (!data || data.length === 0) { $('#currentlyReadingSection').hide(); return; }
            $('#currentlyReadingSection').show();
            var html = '';
            data.forEach(function (e) {
                html += '<div class="reading-card" onclick="openEbookReader(' + e.MaterialID + ', \'' + escapeAttr(e.Title) + '\')">' +
                    '<div class="reading-title">' + e.Title + '</div>' +
                    '<div class="reading-meta">' + e.Author + ' · Opened ' + e.AccessCount + 'x</div>' +
                '</div>';
            });
            $('#currentlyReadingCards').html(html);
        }
    });
}

// ── my requests ───────────────────────────────────────────────────

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
                    ? '<button class="cancel-btn" onclick="cancelRequest(' + r.RequestID + ')">Cancel</button>' : '';
                var pill = '<span class="status-pill s-' + r.Status.toLowerCase() + '">' + r.Status + '</span>';
                rows += '<tr><td>' + r.Title + '</td><td>' + r.TypeName + '</td><td>' + r.RequestDate +
                    '</td><td>' + pill + '</td><td>' + cancelBtn + '</td></tr>';
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
            if (res.success) { showSuccess(res.message); loadMyRequests(); loadCatalog(); }
            else showError(res.message);
        }
    });
}

// ── my borrows ────────────────────────────────────────────────────

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
                var pill    = '<span class="status-pill s-' + b.Status.toLowerCase() + '">' + b.Status + '</span>';
                var dueNote = '';
                if (b.Status === 'Overdue') {
                    var diff = Math.floor((new Date() - new Date(b.DueDate)) / 86400000);
                    dueNote  = ' <span style="color:#c0392b;font-size:.75rem;">(' + diff + 'd overdue)</span>';
                }
                rows += '<tr><td>' + b.Title + '</td><td>' + b.TypeName + '</td><td>' + (b.BorrowDate || '—') +
                    '</td><td>' + b.DueDate + dueNote + '</td><td>' + pill + '</td></tr>';
            });
            $('#myBorrowsTable tbody').html(rows);
        }
    });
}

// ── books to claim ────────────────────────────────────────────────

function loadPendingClaims() {
    $.ajax({
        url: 'app/Controllers/borrowController.php',
        method: 'GET',
        data: { action: 'getPendingClaims' },
        dataType: 'json',
        success: function (data) { allPendingClaims = data || []; renderPendingClaims(allPendingClaims); }
    });
}

function renderPendingClaims(data) {
    if (!data || data.length === 0) {
        $('#pendingClaimsTable tbody').html('<tr><td colspan="5" style="color:#aaa;text-align:center;">No books to claim</td></tr>');
        return;
    }
    var rows = '';
    data.forEach(function (c) {
        var today     = new Date().toISOString().split('T')[0];
        var deadStyle = c.ClaimDeadline && c.ClaimDeadline < today ? 'color:red;font-weight:bold;' : '';
        var deadline  = c.ClaimDeadline || 'Not set';
        var countdown = '';
        if (c.ClaimDeadline) {
            var diff  = Math.ceil((new Date(c.ClaimDeadline) - new Date()) / 86400000);
            countdown = diff > 0
                ? ' <span style="color:#1a7a4a;font-size:.75rem;">(' + diff + 'd left)</span>'
                : ' <span style="color:#c0392b;font-size:.75rem;">(expired)</span>';
        }
        rows += '<tr style="cursor:pointer;" onclick="openCdPopup(\'' +
            escapeAttr(c.Title) + '\',\'' + escapeAttr(c.Author) + '\',\'' +
            c.TypeName + '\',' + c.AvailableQuantity + ',\'' +
            escapeAttr(c.Description || '') + '\',\'' + (c.ClaimDeadline || '') + '\')">' +
            '<td>' + c.Title + '</td><td>' + c.TypeName + '</td><td>' + c.ProcessedDate +
            '</td><td style="' + deadStyle + '">' + deadline + countdown +
            '</td><td><button class="cancel-btn" onclick="event.stopPropagation();cancelClaim(' + c.RequestID + ')">Cancel</button></td></tr>';
    });
    $('#pendingClaimsTable tbody').html(rows);
}

function filterPendingClaims() {
    var q = $('#claimSearch').val().toLowerCase();
    renderPendingClaims(!q ? allPendingClaims : allPendingClaims.filter(function (c) {
        return c.Title.toLowerCase().includes(q);
    }));
}

function cancelClaim(requestID) {
    if (!confirm('Cancel this claim? The book will go back to available stock.')) return;
    $.ajax({
        url: 'app/Controllers/borrowController.php',
        method: 'POST',
        data: { action: 'cancelClaim', requestID: requestID },
        dataType: 'json',
        success: function (res) {
            if (res.success) { showSuccess(res.message); loadPendingClaims(); loadCatalog(); }
            else showError(res.message);
        }
    });
}

function openCdPopup(title, author, type, stock, desc, deadline) {
    $('#cdTitle').text(title); $('#cdAuthor').text(author); $('#cdType').text(type);
    $('#cdStock').text(stock); $('#cdDesc').text(desc || 'No description'); $('#cdDeadline').text(deadline || 'Not set');
    $('#claimDetailPopup').fadeIn(150);
}
function closeCdPopup() { $('#claimDetailPopup').fadeOut(150); }

// ── donate ────────────────────────────────────────────────────────

function openDonateModal() { $('#donateModal').fadeIn(150); }
function closeDonateModal() { $('#donateModal').fadeOut(150); $('#donateForm')[0].reset(); }

function submitDonation() {
    var title  = $('#donateTitle').val().trim();
    var author = $('#donateAuthor').val().trim();
    if (!title || !author) { alert('Title and author are required'); return; }
    $.ajax({
        url: 'app/Controllers/borrowController.php',
        method: 'POST',
        data: { action: 'donate', title: title, author: author,
            genre: $('#donateGenre').val().trim(), description: $('#donateDesc').val().trim(),
            condition: $('#donateCond').val() },
        dataType: 'json',
        success: function (res) {
            closeDonateModal();
            if (res.success) showSuccess(res.message); else showError(res.message);
        }
    });
}

// ── notifications ─────────────────────────────────────────────────

function toggleNotifications() {
    var $p = $('#notificationsPanel');
    if ($p.is(':hidden')) { loadNotifications(); $p.fadeIn(200); } else $p.fadeOut(200);
}

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

// ── search + type filter ──────────────────────────────────────────

function doMatSearch(q) {
    var type = $('#typeFilter').val();
    if (!q && !type) { loadCatalog(); return; }
    $.ajax({
        url: 'app/Controllers/borrowController.php',
        method: 'GET', data: { action: 'search', q: q, type: type }, dataType: 'json',
        success: function (data) {
            renderMatTable('#allTable', data);
            showTab('all', $('.tab-btn').last()[0]);
        }
    });
}

function escapeAttr(str) { return str.replace(/'/g, "\\'").replace(/"/g, '&quot;'); }