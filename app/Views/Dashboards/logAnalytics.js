$(document).ready(function () {
    loadSummary();
    loadBorrowsMonthly();
    loadTopBorrowed();
    loadStatusBreakdown();
    loadMatTypes();
});

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
            let values = data.map(function (d) { return d.Total; });

            new Chart(document.getElementById('chartBorrowsPerMonth'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Borrows',
                        data: values,
                        borderColor: '#4e73df',
                        backgroundColor: 'rgba(78,115,223,0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
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
            var labels = data.map(function (d) { return d.Title; });
            var values = data.map(function (d) { return d.BorrowCount; });

            new Chart(document.getElementById('chartTopBorrowed'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Times Borrowed',
                        data: values,
                        backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b']
                    }]
                },
                options: {
                    responsive: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
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
            let values = data.map(function (d) { return d.Total; });

            new Chart(document.getElementById('chartStatusBreakdown'), {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{ data: values, backgroundColor: ['#1cc88a', '#4e73df', '#e74a3b'] }]
                },
                options: {
                    responsive: false,
                    plugins: { legend: { position: 'bottom' } }
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
            let values = data.map(function (d) { return d.Total; });

            new Chart(document.getElementById('chartMaterialTypes'), {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{ data: values, backgroundColor: ['#4e73df', '#1cc88a', '#f6c23e'] }]
                },
                options: {
                    responsive: false,
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }
    });
}