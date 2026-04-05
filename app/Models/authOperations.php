<?php

require_once __DIR__ . "/../../config/dbConn.php";

$conn = getConnection();

//get staff row by email
function staffEmail($email) {
    global $conn;

    $stmt = $conn->prepare("
        SELECT s.*, sr.RoleName AS Role, ss.StatusName AS Status
        FROM Staffs s
        JOIN StaffRoles sr ON s.RoleID = sr.RoleID
        JOIN StaffStatus ss ON s.StatusID = ss.StatusID
        WHERE s.Email = ?
        LIMIT 1
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->num_rows > 0 ? $result->fetch_assoc() : null;
}

//get member row by email
function memberEmail($email) {
    global $conn;

    $stmt = $conn->prepare("
        SELECT m.*, ms.StatusName AS Status
        FROM Members m
        JOIN MemberStatus ms ON m.StatusID = ms.StatusID
        WHERE m.Email = ?
        LIMIT 1
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->num_rows > 0 ? $result->fetch_assoc() : null;
}

function updStaffPass($id, $hashed) {
    $conn = getConnection();
    $stmt = $conn->prepare("UPDATE Staffs SET Password = ?, DefaultPassword = 0 WHERE StaffID = ?");
    $stmt->bind_param("si", $hashed, $id);
    return $stmt->execute();
}

function updMemPass($id, $hashed) {
    $conn = getConnection();
    $stmt = $conn->prepare("UPDATE Members SET Password = ?, DefaultPassword = 0 WHERE MemberID = ?");
    $stmt->bind_param("si", $hashed, $id);
    return $stmt->execute();
}

function logLogin($userType, $userID, $email, $status, $ip) {
    global $conn;

    $stmt = $conn->prepare("
        INSERT INTO LoginLogs (UserType, UserID, Email, Status, IPAddress)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sisss", $userType, $userID, $email, $status, $ip);
    $stmt->execute();
}
?>