const CTRL = 'app/Controllers/staffController.php';

$(document).ready(function () {
    loadDropdowns();
    loadStaff();

    $('#staffForm').hide();

    $('#toggleBtn').click(function () {
        $('#editForm').hide();
        $('#staffForm').fadeToggle(300);
    });
});

function loadDropdowns() {
    $.ajax({
        url: CTRL,
        method: 'GET',
        data: { action: 'getRoles' },
        dataType: 'json',
        success: function (data) {
            let option = data.map(r => `<option value="${r.RoleID}">${r.RoleName}</option>`).join('');
            $('#addRole, #editRole').html(option);
        }
    });

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

function loadStaff() {
    $.ajax({
        url: CTRL,
        method: 'GET',
        data: { action: 'getAll' },
        cache: false,
        dataType: 'json',
        success: function (data) {
            let rows = '';
            data.forEach(s => {
                rows += `
                <tr>
                    <td>${s.StaffID}</td>
                    <td>${s.FirstName} ${s.LastName}</td>
                    <td>${s.Email}</td>
                    <td>${s.RoleName}</td>
                    <td>${s.StatusName}</td>
                    <td>${s.DefaultPassword == 1 ? 'Yes' : 'No'}</td>
                    <td>${s.DateCreated}</td>
                    <td>
                        <button class="btn-edit" onclick="loadEdit(${s.StaffID})">Edit</button>
                        <button class="btn-archive" onclick="archiveStaff(${s.StaffID})">Archive</button>
                    </td>
                </tr>`;
            });
            $('#staffTable tbody').html(rows);
        },
        error: function (xhr) {
            console.log('loadStaff error:', xhr.responseText);
        }
    });
}

function addStaff() {
    hideError();
    let fname = $('#fname').val().trim();
    let lname = $('#lname').val().trim();
    let email = $('#email').val().trim();
    let roleID = $('#addRole').val();
    let statusID = $('#addStatus').val();

    if (!fname) return showError('First Name is required');
    if (!lname) return showError('Last Name is required');
    if (!email) return showError('Email is required');

    $.ajax({
        url: CTRL,
        method: 'POST',
        data: { action: 'addStaff', fname, lname, email, roleID, statusID },
        dataType: 'json',
        success: function (res) {
            if (res.success) {
                alert(res.message);
                $('#staffForm').hide();
                clearAddForm();
                loadStaff();
            } else {
                showError(res.message);
            }
        }
    });
}

function clearAddForm() {
    $('#fname, #lname, #email').val('');
}

function loadEdit(id) {
    $.ajax({
        url: CTRL,
        method: 'GET',
        data: { action: 'getOne', id: id },
        dataType: 'json',
        success: function (s) {
            $('#editStaffID').val(s.StaffID);
            $('#editFname').val(s.FirstName);
            $('#editLname').val(s.LastName);
            $('#editEmail').val(s.Email);
            $('#editRole').val(s.RoleID);
            $('#editStatus').val(s.StatusID);
            $('#staffForm').hide();
            $('#editForm').fadeIn(300);
        }
    });
}

function updStaff() {
    hideError();
    let staffID = $('#editStaffID').val();
    let fname = $('#editFname').val().trim();
    let lname = $('#editLname').val().trim();
    let email = $('#editEmail').val().trim();
    let roleID = $('#editRole').val();
    let statusID = $('#editStatus').val();

    if (!fname) return showError('First Name is required');
    if (!lname) return showError('Last Name is required');
    if (!email) return showError('Email is required');

    $.ajax({
        url: CTRL,
        method: 'POST',
        data: { action: 'updStaff', staffID, fname, lname, email, roleID, statusID },
        dataType: 'json',
        success: function (res) {
            if (res.success) {
                alert(res.message);
                $('#editForm').hide();
                loadStaff();
            } else {
                showError(res.message);
            }
        }
    });
}

function archiveStaff(id) {
    if (!confirm('Archive this staff? Their data will be saved to archives.')) return;
    $.ajax({
        url: CTRL,
        method: 'POST',
        data: { action: 'archiveStaff', staffID: id },
        dataType: 'json',
        success: function (res) {
            alert(res.message);
            if (res.success) location.reload();
        }
    });
}

function doStaffSearch(q) {
    if (q === '') {
        loadStaff();
        return;
    }
    $.ajax({
        url: CTRL,
        method: 'GET',
        data: { action: 'search', q: q },
        cache: false,
        dataType: 'json',
        success: function (data) {
            let rows = '';
            if (!data || data.length === 0) {
                rows = '<tr><td colspan="8">No staff found</td></tr>';
            } else {
                data.forEach(s => {
                    rows += `
                    <tr>
                        <td>${s.StaffID}</td>
                        <td>${s.FirstName} ${s.LastName}</td>
                        <td>${s.Email}</td>
                        <td>${s.RoleName}</td>
                        <td>${s.StatusName}</td>
                        <td>${s.DefaultPassword == 1 ? 'Yes' : 'No'}</td>
                        <td>${s.DateCreated}</td>
                        <td>
                            <button onclick="loadEdit(${s.StaffID})">Edit</button>
                            <button onclick="archiveStaff(${s.StaffID})">Archive</button>
                        </td>
                    </tr>`;
                });
            }
            $('#staffTable tbody').html(rows);
        }
    });
}

function showError(text) {
    $('#error_text').text(text).fadeIn();
}

function hideError() {
    $('#error_text').hide();
}