let loadedTabs = {};

$(document).ready(function () {
    loadTab('borrowLogs');

    bindSearch('searchBorrowLogs','tblBorrowLogs','countBorrowLogs');
    bindSearch('searchLoginLogs','tblLoginLogs','countLoginLogs');
    bindSearch('searchStaffLogs','tblStaffLogs','countStaffLogs');
    bindSearch('searchMemberLogs','tblMemberLogs','countMemberLogs');
    bindSearch('searchMaterialLogs','tblMaterialLogs','countMaterialLogs');
    bindSearch('searchArchivesStaff','tblArchivesStaff','countArchivesStaff');
    bindSearch('searchArchivesMember','tblArchivesMember','countArchivesMember');


    $(document).on('click', '.btn-view-json', function () {
        viewJson($(this).attr('data-json'));
    });
});

function switchTab(tabKey, el) {
    $('.page-tab').removeClass('active');
    $(el).addClass('active');

    $('.page-pane').removeClass('active');
    $('#pane-' + tabKey).addClass('active');

    if (!loadedTabs[tabKey]) {
        loadTab(tabKey);
    }
}

function loadTab(tabKey) {
    switch (tabKey) {
        case 'borrowLogs':
            fetchLogs('getBorrowLogs', null, renderBorrowLogs, 'tblBorrowLogs', 'countBorrowLogs');
            break;
        case 'loginLogs':
            fetchLogs('getLoginLogs', null, renderLoginLogs, 'tblLoginLogs', 'countLoginLogs');
            break;
        case 'staffLogs':
            fetchLogs('getStaffLogs', null, renderStaffLogs, 'tblStaffLogs', 'countStaffLogs');
            break;
        case 'memberLogs':
            fetchLogs('getMemberLogs', null, renderMemberLogs, 'tblMemberLogs', 'countMemberLogs');
            break;
        case 'materialLogs':
            fetchLogs('getMaterialLogs', null, renderMaterialLogs, 'tblMaterialLogs', 'countMaterialLogs');
            break;
        case 'archivesStaff':
            fetchLogs('getArchives', { type: 'Staff' }, renderArchives, 'tblArchivesStaff', 'countArchivesStaff');
            break;
        case 'archivesMember':
            fetchLogs('getArchives', { type: 'Member' }, renderArchives, 'tblArchivesMember', 'countArchivesMember');
            break;
    }
    
    loadedTabs[tabKey] = true;
}

function fetchLogs(action, extraData, renderFn, tblId, countId) {
    let params = { action: action };
    if (extraData) $.extend(params, extraData);

    $('#' + tblId + ' tbody').html('<tr class="empty-row"><td colspan="10">Loading...</td></tr>');

    $.ajax({
        url: 'app/Controllers/logController.php',
        method: 'GET',
        data: params,
        dataType: 'json',
        success: function (resp) {
            if (resp.success && resp.data.length > 0) {
                renderFn(resp.data, tblId, countId);
            } else {
                $('#' + tblId + ' tbody').html('<tr class="empty-row"><td colspan="10">No records found.</td></tr>');
                $('#' + countId).text('0 records');
            }
        },
        error: function () {
            $('#' + tblId + ' tbody').html('<tr class="empty-row"><td colspan="10">Failed to load data.</td></tr>');
        }
    });
}

function renderBorrowLogs(data, tblId, countId) {
    let html = '';
    data.forEach(function (r, i) {
        html += '<tr>' +
            '<td>' + (i + 1) + '</td>' +
            '<td>' + r.LogTime + '</td>' +
            '<td>' + badge(r.Action) + '</td>' +
            '<td>' + esc(r.MemberName) + '</td>' +
            '<td>' + (r.StaffName || '<span style="color:#aaa">—</span>') + '</td>' +
            '<td>' + esc(r.Material) + '</td>' +
        '</tr>';
    });
    $('#' + tblId + ' tbody').html(html);
    $('#' + countId).text(data.length + ' records');
}

function renderLoginLogs(data, tblId, countId) {
    let html = '';
    data.forEach(function (r, i) {
        html += '<tr>' +
            '<td>' + (i + 1) + '</td>' +
            '<td>' + r.LogTime + '</td>' +
            '<td>' + badge(r.UserType) + '</td>' +
            '<td>' + esc(r.Email) + '</td>' +
            '<td>' + badge(r.Status) + '</td>' +
            '<td>' + (r.IPAddress || '—') + '</td>' +
        '</tr>';
    });
    $('#' + tblId + ' tbody').html(html);
    $('#' + countId).text(data.length + ' records');
}

function renderStaffLogs(data, tblId, countId) {
    let html = '';
    data.forEach(function (r, i) {
        html += '<tr>' +
            '<td>' + (i + 1) + '</td>' +
            '<td>' + r.LogTime + '</td>' +
            '<td>' + badge(r.Action) + '</td>' +
            '<td>' + esc(r.AdminName) + '</td>' +
            '<td>' + esc(r.AffectedStaff) + '</td>' +
            '<td>' + esc(r.AffectedRole) + '</td>' +
        '</tr>';
    });
    $('#' + tblId + ' tbody').html(html);
    $('#' + countId).text(data.length + ' records');
}

function renderMemberLogs(data, tblId, countId) {
    let html = '';
    data.forEach(function (r, i) {
        html += '<tr>' +
            '<td>' + (i + 1) + '</td>' +
            '<td>' + r.LogTime + '</td>' +
            '<td>' + badge(r.Action) + '</td>' +
            '<td>' + esc(r.StaffName) + '</td>' +
            '<td>' + esc(r.AffectedMember) + '</td>' +
            '<td>' + esc(r.MemberEmail) + '</td>' +
        '</tr>';
    });
    $('#' + tblId + ' tbody').html(html);
    $('#' + countId).text(data.length + ' records');
}

function renderMaterialLogs(data, tblId, countId) {
    let html = '';
    data.forEach(function (r, i) {
        html += '<tr>' +
            '<td>' + (i + 1) + '</td>' +
            '<td>' + r.LogTime + '</td>' +
            '<td>' + badge(r.Action) + '</td>' +
            '<td>' + esc(r.StaffName) + '</td>' +
            '<td>' + esc(r.Material) + '</td>' +
        '</tr>';
    });
    $('#' + tblId + ' tbody').html(html);
    $('#' + countId).text(data.length + ' records');
}

function renderArchives(data, tblId, countId) {
    let html = '';
    data.forEach(function (r, i) {
        let shortData = r.ArchivedData.substring(0, 60) + '...';
        html += '<tr>' +
            '<td>' + (i + 1) + '</td>' +
            '<td>' + r.DateRemoved + '</td>' +
            '<td>' + badge(r.EntityType) + '</td>' +
            '<td>' + r.EntityID + '</td>' +
            '<td><span class="archive-data">' + esc(shortData) + '</span></td>' +
            '<td><button class="btn-view-json" data-json=\'' + esc(r.ArchivedData) + '\'>View</button></td>' +
        '</tr>';
    });
    $('#' + tblId + ' tbody').html(html);
    $('#' + countId).text(data.length + ' records');
}

function bindSearch(inputId, tblId, countId) {
    $('#' + inputId).on('keyup', function () {
        let term = $(this).val().toLowerCase();
        let visible = 0;
        $('#' + tblId + ' tbody tr').each(function () {
            let text = $(this).text().toLowerCase();
            if (text.indexOf(term) > -1) {
                $(this).show();
                visible++;
            } else {
                $(this).hide();
            }
        });
        $('#' + countId).text(visible + ' records');
    });
}

function viewJson(raw) {
    try {
        let parsed = JSON.parse(raw);
        $('#jsonContent').text(JSON.stringify(parsed, null, 2));
    } catch(e) {
        $('#jsonContent').text(raw);
    }
    $('#jsonModal').addClass('open');
}

function closeJson() {
    $('#jsonModal').removeClass('open');
}

function badge(val) {
    if (!val) return '<span style="color:#aaa">—</span>';
    let cls = 'badge badge-' + val.toLowerCase().replace(/\s/g, '');
    return '<span class="' + cls + '">' + esc(val) + '</span>';
}

function esc(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}