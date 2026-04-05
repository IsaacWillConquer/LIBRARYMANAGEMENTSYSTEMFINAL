$(document).ready(function () {
    loadHistory();
});

function loadHistory() {
    $.ajax({
        url: 'app/Controllers/borrowController.php',
        method: 'GET',
        data: { action: 'getHistory' },
        dataType: 'json',
        success: function (data) {
            if (!data || data.length === 0) {
                $('#historyTable tbody').html('<tr><td colspan="8">No borrow history found</td></tr>');
                return;
            }

            var rows = '';
            data.forEach(function (b) {
                var statusStyle = b.Status === 'Overdue' ? 'color:red;font-weight:bold;' : '';
                var returnDate = b.ReturnDate ? b.ReturnDate : '-';

                var fineText = '-';
                if (b.OverdueFine > 0) {
                    var due = new Date(b.DueDate);
                    var returned = b.ReturnDate ? new Date(b.ReturnDate) : new Date();
                    var days = Math.floor((returned - due) / (1000 * 60 * 60 * 24));
                    var rate = (parseFloat(b.OverdueFine) / days).toFixed(2);
                    fineText = days + ' day(s) × ₱' + rate + '/day = ₱' + parseFloat(b.OverdueFine).toFixed(2);
                }

                rows += '<tr>' +
                    '<td>' + b.Title + '</td>' +
                    '<td>' + b.Author + '</td>' +
                    '<td>' + b.TypeName + '</td>' +
                    '<td>' + b.BorrowDate + '</td>' +
                    '<td>' + b.DueDate + '</td>' +
                    '<td>' + returnDate + '</td>' +
                    '<td style="' + statusStyle + '">' + b.Status + '</td>' +
                    '<td>' + fineText + '</td>' +
                '</tr>';
            });

            $('#historyTable tbody').html(rows);
        }
    });
}