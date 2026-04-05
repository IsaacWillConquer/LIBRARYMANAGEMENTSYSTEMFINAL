<?php

require_once __DIR__ . "/../../config/dbConn.php";

$conn = getConnection();

//get all staff except the logged in admin
function getStaff($adminID) {
    global $conn;

    $stmt = $conn->prepare("
        SELECT s.StaffID, s.FirstName, s.LastName, s.Email,
               s.DefaultPassword, s.DateCreated,
               r.RoleID, r.RoleName,
               st.StatusID, st.StatusName
        FROM staffs s
        JOIN staffroles r ON s.RoleID = r.RoleID
        JOIN staffstatus st ON s.StatusID = st.StatusID
        WHERE s.StaffID != ?
        ORDER BY s.StaffID DESC
    ");
    $stmt->bind_param('i', $adminID);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) $data[] = $row;
    return $data;
}

//get all roles, exclude admin
function getRoles() {
    global $conn;

    $result = $conn->query("SELECT * FROM staffroles WHERE RoleName != 'Admin'");
    $data = [];
    while ($row = $result->fetch_assoc()) $data[] = $row;
    return $data;
}

//get all statuses
function getStatuses() {
    global $conn;

    $result = $conn->query("SELECT * FROM staffstatus");
    $data = [];
    while ($row = $result->fetch_assoc()) $data[] = $row;
    return $data;
}

//get single staff by id
function getStaffByID($id) {
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM staffs WHERE StaffID = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

//check if email alr exists
function emailExists($email, $excludeID = null) {
    global $conn;

    if ($excludeID) {
        $stmt = $conn->prepare("SELECT StaffID FROM staffs WHERE Email = ? AND StaffID != ?");
        $stmt->bind_param('si', $email, $excludeID);
    } else {
        $stmt = $conn->prepare("SELECT StaffID FROM staffs WHERE Email = ?");
        $stmt->bind_param('s', $email);
    }

    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

//add new staff, default pass is Password
function addStaff($fname, $lname, $email, $roleID, $statusID, $adminID) {
    global $conn;

    if (emailExists($email)) {
        return ['success' => false, 'message' => 'This Email already exists'];
    }

    $defaultPass = password_hash('Password', PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
        INSERT INTO staffs (FirstName, LastName, Email, Password, DefaultPassword, RoleID, StatusID)
        VALUES (?, ?, ?, ?, 1, ?, ?)
    ");
    $stmt->bind_param('ssssii', $fname, $lname, $email, $defaultPass, $roleID, $statusID);

    if (!$stmt->execute()) {
        return ['success' => false, 'message' => $conn->error];
    }

    $newID = $conn->insert_id;
    logStaffAction($adminID, $newID, 'Created');

    return ['success' => true, 'message' => 'Staff added. Default password is: Password'];
}

//update staff info
function updStaff($staffID, $fname, $lname, $email, $roleID, $statusID, $adminID) {
    global $conn;

    if (emailExists($email, $staffID)) {
        return ['success' => false, 'message' => 'Email already used by another staff'];
    }

    $stmt = $conn->prepare("
        UPDATE staffs SET FirstName=?, LastName=?, Email=?, RoleID=?, StatusID=?
        WHERE StaffID=?
    ");
    $stmt->bind_param('sssiii', $fname, $lname, $email, $roleID, $statusID, $staffID);

    if (!$stmt->execute()) {
        return ['success' => false, 'message' => $conn->error];
    }

    logStaffAction($adminID, $staffID, 'Updated');
    return ['success' => true, 'message' => 'Staff updated successfully'];
}

//hard delete with json snapshot saved to archives
function archiveStaff($staffID, $adminID) {
    global $conn;

    if ($staffID == $adminID) {
        return ['success' => false, 'message' => 'You cannot archive your own account'];
    }

    $staff = getStaffByID($staffID);
    if (!$staff) {
        return ['success' => false, 'message' => 'Staff not found'];
    }

    $json = $conn->real_escape_string(json_encode($staff));
    $archiveResult = $conn->query("
        INSERT INTO archives (EntityType, EntityID, ArchivedData)
        VALUES ('Staff', $staffID, '$json')
    ");

    if (!$archiveResult) {
        return ['success' => false, 'message' => 'Archive failed: ' . $conn->error];
    }

    //log before delete so FK doesnt blow up
    logStaffAction($adminID, $staffID, 'Archived');

    $stmt = $conn->prepare("DELETE FROM stafflogs WHERE AffectedStaffID = ?");
    $stmt->bind_param('i', $staffID);
    $stmt->execute();

    $stmt = $conn->prepare("DELETE FROM staffs WHERE StaffID = ?");
    $stmt->bind_param('i', $staffID);

    if (!$stmt->execute()) {
        return ['success' => false, 'message' => $conn->error];
    }

    return ['success' => true, 'message' => 'Staff archived successfully'];
}

function searchStaff($q) {
    global $conn;

    $like = '%' . $conn->real_escape_string($q) . '%';

    $result = $conn->query("
        SELECT s.*, sr.RoleName, ss.StatusName
        FROM staffs s
        JOIN staffroles sr ON s.RoleID = sr.RoleID
        JOIN staffstatus ss ON s.StatusID = ss.StatusID
        WHERE s.StaffID LIKE '$like'
           OR s.FirstName LIKE '$like'
           OR s.LastName LIKE '$like'
           OR s.Email LIKE '$like'
           OR CONCAT(s.FirstName, ' ', s.LastName) LIKE '$like'
           OR sr.RoleName LIKE '$like'
        ORDER BY s.StaffID DESC
    ");

    $data = [];
    while ($row = $result->fetch_assoc()) $data[] = $row;
    return $data;
}

//log staff actions
function logStaffAction($adminID, $affectedID, $action) {
    global $conn;

    $stmt = $conn->prepare("
        INSERT INTO stafflogs (AdminID, AffectedStaffID, Action)
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param('iis', $adminID, $affectedID, $action);
    $stmt->execute();
}
?>