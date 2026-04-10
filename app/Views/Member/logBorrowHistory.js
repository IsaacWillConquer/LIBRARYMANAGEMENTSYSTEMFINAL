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

            let rows = '';
            data.forEach(function (b) {
                let statusStyle = b.Status === 'Overdue' ? 'color:red;font-weight:bold;' : '';
                var returnDate = b.ReturnDate ? b.ReturnDate : '-';

                let fineText = '-';
                if (b.OverdueFine > 0) {
                    let due = new Date(b.DueDate);
                    let returned = b.ReturnDate ? new Date(b.ReturnDate) : new Date();
                    let days = Math.floor((returned - due) / (1000 * 60 * 60 * 24));
                    let rate = (parseFloat(b.OverdueFine) / days).toFixed(2);
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