
let chartInstances = {};

$(document).ready(function () {
    loadAll();
});

function loadAll() {
    loadSummary();
    loadBorrowsMonthly();
    loadTopBorrowed();
    loadStatusBreakdown();
    loadMatTypes();
    loadGenreStats();
    loadTopMembers();
    loadRecentActivity();

    let now = new Date();
    $('#lastUpdated').text('Updated ' + now.toLocaleTimeString());
}

function makeChart(id, config) {
    if (chartInstances[id]) {
        chartInstances[id].destroy();
    }
    chartInstances[id] = new Chart(document.getElementById(id), config);
}

function loadSummary() {
    $.ajax({
        url: 'app/Controllers/analyticsController.php',
        method: 'GET',
        data: { action: 'getSummary' },
        dataType: 'json',
        success: function (data) {
            $('#statMembers').text(data.totalMembers);
            $('#statMaterials').text(data.totalMaterials);
            $('#statActive').text(data.activeBorrows);
            $('#statOverdue').text(data.overdueCount);
            $('#statFines').text('₱' + data.totalFines);
            $('#statPending').text(data.pendingReqs);
            $('#statDonations').text(data.pendingDonations);
        }
    });
}

function loadBorrowsMonthly() {
    $.ajax({
        url: 'app/Controllers/analyticsController.php',
        method: 'GET',
        data: { action: 'getBorrowsMonthly' },
        dataType: 'json',
        success: function (data) {
            let labels = data.map(function (d) { return d.Month; });
            let values = data.map(function (d) { return parseInt(d.Total); });

            makeChart('chartBorrowsPerMonth', {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Borrows',
                        data: values,
                        borderColor: '#4e73df',
                        backgroundColor: 'rgba(78,115,223,0.08)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3,
                        pointBackgroundColor: '#4e73df',
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } }
                    }
                }
            });
        }
    });
}

function loadTopBorrowed() {
    $.ajax({
        url: 'app/Controllers/analyticsController.php',
        method: 'GET',
        data: { action: 'getTopBorrow' },
        dataType: 'json',
        success: function (data) {
            let labels = data.map(function (d) {
                return d.Title.length > 22 ? d.Title.substring(0, 22) + '…' : d.Title;
            });
            let values = data.map(function (d) { return parseInt(d.BorrowCount); });

            makeChart('chartTopBorrowed', {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Times Borrowed',
                        data: values,
                        backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
                        borderRadius: 3
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } }
                    }
                }
            });
        }
    });
}

function loadStatusBreakdown() {
    $.ajax({
        url: 'app/Controllers/analyticsController.php',
        method: 'GET',
        data: { action: 'getborrowStats' },
        dataType: 'json',
        success: function (data) {
            let labels = data.map(function (d) { return d.Status; });
            let values = data.map(function (d) { return parseInt(d.Total); });
            let colors = {
                'Borrowed': '#4e73df',
                'Returned': '#1cc88a',
                'Overdue':  '#e74a3b'
            };
            let bgColors = labels.map(function (l) { return colors[l] || '#aaa'; });

            makeChart('chartStatusBreakdown', {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{ data: values, backgroundColor: bgColors, borderWidth: 1 }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } }
                }
            });
        }
    });
}

function loadMatTypes() {
    $.ajax({
        url: 'app/Controllers/analyticsController.php',
        method: 'GET',
        data: { action: 'getMatType' },
        dataType: 'json',
        success: function (data) {
            let labels = data.map(function (d) { return d.TypeName; });
            let values = data.map(function (d) { return parseInt(d.Total); });

            makeChart('chartMaterialTypes', {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{ data: values, backgroundColor: ['#4e73df', '#1cc88a', '#f6c23e'], borderWidth: 1 }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } }
                }
            });
        }
    });
}

function loadGenreStats() {
    $.ajax({
        url: 'app/Controllers/analyticsController.php',
        method: 'GET',
        data: { action: 'getGenreStats' },
        dataType: 'json',
        success: function (data) {
            let labels = data.map(function (d) { return d.Genre; });
            let values = data.map(function (d) { return parseInt(d.BorrowCount); });

            makeChart('chartGenre', {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Borrows',
                        data: values,
                        backgroundColor: '#36b9cc',
                        borderRadius: 3
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 } }
                    }
                }
            });
        }
    });
}

function loadTopMembers() {
    $.ajax({
        url: 'app/Controllers/analyticsController.php',
        method: 'GET',
        data: { action: 'getTopMembers' },
        dataType: 'json',
        success: function (data) {
            if (!data || data.length === 0) {
                $('#topMembersBody').html('<tr><td colspan="3" style="color:#aaa;">No data yet</td></tr>');
                return;
            }
            let rows = '';
            data.forEach(function (m, i) {
                rows += '<tr>' +
                    '<td class="rank">' + (i + 1) + '</td>' +
                    '<td>' + m.MemberName + '</td>' +
                    '<td>' + m.BorrowCount + '</td>' +
                '</tr>';
            });
            $('#topMembersBody').html(rows);
        }
    });
}

function loadRecentActivity() {
    $.ajax({
        url: 'app/Controllers/analyticsController.php',
        method: 'GET',
        data: { action: 'getRecentActivity' },
        dataType: 'json',
        success: function (data) {
            if (!data || data.length === 0) {
                $('#activityFeed').html('<div class="activity-item" style="color:#aaa;">No activity yet</div>');
                return;
            }
            let html = '';
            data.forEach(function (a) {
                html += '<div class="activity-item">' +
                    '<span class="activity-action a-' + a.Action + '">' + a.Action + '</span>' +
                    '<div>' +
                        '<div class="activity-text"><strong>' + a.MemberName + '</strong> — ' + a.Title + '</div>' +
                        '<div class="activity-time">' + a.LogTime + '</div>' +
                    '</div>' +
                '</div>';
            });
            $('#activityFeed').html(html);
        }
    });
}