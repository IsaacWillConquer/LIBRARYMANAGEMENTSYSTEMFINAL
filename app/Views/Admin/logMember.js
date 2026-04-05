const CTRL = 'app/Controllers/memberController.php';

$(document).ready(function () {
    loadStatuses();
    loadMembers();

    $('#memberForm').hide();

    $('#toggleBtn').click(function () {
        $('#editForm').hide();
        $('#memberForm').fadeToggle(300);
    });
});

function loadStatuses() {
    $.ajax({
        url: CTRL,
        method: 'GET',
        data: { action: 'getStatuses' },
        dataType: 'json',
        success: function (data) {
            let option = data.map(s => `<option value="${s.StatusID}">${s.StatusName}</option>`).join('');
            $('#addStatus, #editStatus').html(option);
        }
    });
}

function loadMembers() {
    $.ajax({
        url: CTRL,
        method: 'GET',
        data: { action: 'getAll' },
        cache: false,
        dataType: 'json',
        success: function (data) {
            let rows = '';
            data.forEach(m => {
                rows += `
                <tr>
                    <td>${m.MemberID}</td>
                    <td>${m.FirstName} ${m.LastName}</td>
                    <td>${m.Email}</td>
                    <td>${m.StatusName}</td>
                    <td>${m.DefaultPassword == 1 ? 'Yes' : 'No'}</td>
                    <td>${m.DateCreated}</td>
                    <td>
                        <button onclick="loadEditMember(${m.MemberID})">Edit</button>
                        <button onclick="archiveMember(${m.MemberID})">Archive</button>
                    </td>
                </tr>`;
            });
            $('#memberTable tbody').html(rows);
        },
        error: function (xhr) {
            console.log('loadMembers error:', xhr.responseText);
        }
    });
}

function addMember() {
    hideError();
    let fname = $('#fname').val().trim();
    let lname = $('#lname').val().trim();
    let email = $('#email').val().trim();
    let statusID = $('#addStatus').val();

    if (!fname) return showError('First Name is required');
    if (!lname) return showError('Last Name is required');
    if (!email) return showError('Email is required');

    $.ajax({
        url: CTRL,
        method: 'POST',
        data: { action: 'addMember', fname, lname, email, statusID },
        dataType: 'json',
        success: function (res) {
            if (res.success) {
                alert(res.message);
                $('#memberForm').hide();
                loadMembers();
                clearAddForm();
            } else {
                showError(res.message);
            }
        }
    });
}

function clearAddForm() {
    $('#fname, #lname, #email').val('');
}

function loadEditMember(id) {
    $.ajax({
        url: CTRL,
        method: 'GET',
        data: { action: 'getOne', id: id },
        dataType: 'json',
        success: function (m) {
            $('#editMemberID').val(m.MemberID);
            $('#editFname').val(m.FirstName);
            $('#editLname').val(m.LastName);
            $('#editEmail').val(m.Email);
            $('#editStatus').val(m.StatusID);
            $('#memberForm').hide();
            $('#editForm').fadeIn(300);
        }
    });
}

function updMember() {
    hideError();
    let memberID = $('#editMemberID').val();
    let fname = $('#editFname').val().trim();
    let lname = $('#editLname').val().trim();
    let email = $('#editEmail').val().trim();
    let statusID = $('#editStatus').val();

    if (!fname) return showError('First Name is required');
    if (!lname) return showError('Last Name is required');
    if (!email) return showError('Email is required');

    $.ajax({
        url: CTRL,
        method: 'POST',
        data: { action: 'updMember', memberID, fname, lname, email, statusID },
        dataType: 'json',
        success: function (res) {
            if (res.success) {
                alert(res.message);
                $('#editForm').hide();
                loadMembers();
            } else {
                showError(res.message);
            }
        }
    });
}

function archiveMember(id) {
    if (!confirm('Archive this member? Their data will be saved to archives.')) return;
    $.ajax({
        url: CTRL,
        method: 'POST',
        data: { action: 'archiveMember', memberID: id },
        dataType: 'json',
        success: function (res) {
            alert(res.message);
            if (res.success) loadMembers();
        }
    });
}

function doMemberSearch(q) {
    if (q === '') {
        loadMembers();
        return;
    }
    $.ajax({
        url: CTRL,
        method: 'GET',
        data: { action: 'search', q: q },
        cache: false,
        dataType: 'json',
        success: function (data) {
            renderMemberTable(data);
        }
    });
}

function renderMemberTable(data) {
    if (!data || data.length === 0) {
        $('#memberTable tbody').html('<tr><td colspan="7">No members found</td></tr>');
        return;
    }

    var rows = '';
    data.forEach(function (m) {
        rows += '<tr>' +
            '<td>' + m.MemberID + '</td>' +
            '<td>' + m.FirstName + ' ' + m.LastName + '</td>' +
            '<td>' + m.Email + '</td>' +
            '<td>' + m.StatusName + '</td>' +
            '<td>' + (m.DefaultPassword == 1 ? 'Yes' : 'No') + '</td>' +
            '<td>' + m.DateCreated + '</td>' +
            '<td>' +
                '<button onclick="loadEditMember(' + m.MemberID + ')">Edit</button> ' +
                '<button onclick="archiveMember(' + m.MemberID + ')">Archive</button>' +
            '</td>' +
        '</tr>';
    });

    $('#memberTable tbody').html(rows);
}

function showError(msg) {
    $('#error_text').text(msg).fadeIn();
}

function hideError() {
    $('#error_text').hide();
}