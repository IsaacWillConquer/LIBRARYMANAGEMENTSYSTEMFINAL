$(document).ready(function () {
    getPendingReq();
    loadActBorrows();

    setInterval(function () {
        getPendingReq();
        loadActBorrows();
    }, 5000);
});

function showError(msg) {
    $('#success_text').hide();
    $('#error_text').text(msg).fadeIn();
}

function showSuccess(msg) {
    $('#error_text').hide();
    $('#success_text').text(msg).fadeIn();
    setTimeout(function () { $('#success_text').fadeOut(); }, 3000);
}

function getPendingReq() {
    $.ajax({
        url: 'app/Controllers/circulationController.php',
        method: 'GET',
        data: { action: 'getPendingReq' },
        dataType: 'json',
        success: function (data) {
            if (!data || data.length === 0) {
                $('#pendingTable tbody').html('<tr><td colspan="6">No pending requests</td></tr>');
                return;
            }

            var rows = '';
            data.forEach(function (r) {
                rows += '<tr>' +
                    '<td>' + r.MemberName + '</td>' +
                    '<td>' + r.Title + '</td>' +
                    '<td>' + r.TypeName + '</td>' +
                    '<td>' + r.Author + '</td>' +
                    '<td>' + r.RequestDate + '</td>' +
                    '<td>' +
                        '<button onclick="approveRequest(' + r.RequestID + ')">Approve</button>' +
                        ' ' +
                        '<button onclick="rejectRequest(' + r.RequestID + ')">Reject</button>' +
                    '</td>' +
                '</tr>';
            });

            $('#pendingTable tbody').html(rows);
        }
    });
}

function approveRequest(requestID) {
    if (!confirm('Approve this borrow request?')) return;

    $.ajax({
        url: 'app/Controllers/circulationController.php',
        method: 'POST',
        data: { action: 'approveReq', requestID: requestID },
        dataType: 'json',
        success: function (res) {
            if (res.success) {
                showSuccess(res.message);
                getPendingReq();
            } else {
                showError(res.message);
            }
        }
    });
}

function rejectRequest(requestID) {
    if (!confirm('Reject this borrow request?')) return;

    $.ajax({
        url: 'app/Controllers/circulationController.php',
        method: 'POST',
        data: { action: 'rejectReq', requestID: requestID },
        dataType: 'json',
        success: function (res) {
            if (res.success) {
                showSuccess(res.message);
                getPendingReq();
            } else {
                showError(res.message);
            }
        }
    });
}

function loadActBorrows() {
    $.ajax({
        url: 'app/Controllers/circulationController.php',
        method: 'GET',
        data: { action: 'getActiveBorrows' },
        dataType: 'json',
        success: function (data) {
            if (!data || data.length === 0) {
                $('#activeBorrowsTable tbody').html('<tr><td colspan="7">No active borrows</td></tr>');
                return;
            }

            var rows = '';
            data.forEach(function (b) {
                var statusStyle = b.Status === 'Overdue' ? 'color:red;font-weight:bold;' : '';

                var daysOverdue = '';
                if (b.Status === 'Overdue') {
                    var due = new Date(b.DueDate);
                    var today = new Date();
                    var diff = Math.floor((today - due) / (1000 * 60 * 60 * 24));
                    daysOverdue = ' (' + diff + ' day(s) overdue)';
                }

                rows += '<tr>' +
                    '<td>' + b.MemberName + '</td>' +
                    '<td>' + b.Title + '</td>' +
                    '<td>' + b.TypeName + '</td>' +
                    '<td>' + b.BorrowDate + '</td>' +
                    '<td>' + b.DueDate + '</td>' +
                    '<td style="' + statusStyle + '">' + b.Status + daysOverdue + '</td>' +
                    '<td><button onclick="returnBook(' + b.RecordID + ', \'' + escapeAttr(b.Title) + '\')">Return</button></td>' +
                '</tr>';
            });

            $('#activeBorrowsTable tbody').html(rows);
        }
    });
}

function returnBook(recordID, title) {
    if (!confirm('Mark "' + title + '" as returned?')) return;

    $.ajax({
        url: 'app/Controllers/circulationController.php',
        method: 'POST',
        data: { action: 'returnBook', recordID: recordID },
        dataType: 'json',
        success: function (res) {
            if (res.success) {
                showSuccess(res.message);
                loadActBorrows();
                getPendingReq();
            } else {
                showError(res.message);
            }
        }
    });
}

function escapeAttr(str) {
    return str.replace(/'/g, "\\'");
}