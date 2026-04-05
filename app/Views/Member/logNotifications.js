$(document).ready(function () {
    loadNotifications();

    setInterval(function () {
        loadNotifications();
    }, 5000);
});

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
        }
    });
}