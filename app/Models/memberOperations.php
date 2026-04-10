<?php

require_once __DIR__ . "/../../config/dbConn.php";

$conn = getConnection();

//get all members with status name
function getMembers() {
    global $conn;

    $result = $conn->query("
        SELECT m.MemberID, m.FirstName, m.LastName, m.Email,
               m.DefaultPassword, m.DateCreated,
               ms.StatusID, ms.StatusName
        FROM members m
        JOIN memberstatus ms ON m.StatusID = ms.StatusID
        ORDER BY m.MemberID DESC
    ");

    $data = [];
    while ($row = $result->fetch_assoc()) $data[] = $row;
    return $data;
}

//get all member statuses
function getMemberStatuses() {
    global $conn;

    $result = $conn->query("SELECT * FROM memberstatus");
    $data = [];
    while ($row = $result->fetch_assoc()) $data[] = $row;
    return $data;
}

//get single member by id
function getMemberByID($id) {
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM members WHERE MemberID = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

//check if email alr exists
function memberEmailExists($email, $excludeID = null) {
    global $conn;

    if ($excludeID) {
        $stmt = $conn->prepare("SELECT MemberID FROM members WHERE Email = ? AND MemberID != ?");
        $stmt->bind_param('si', $email, $excludeID);
    } else {
        $stmt = $conn->prepare("SELECT MemberID FROM members WHERE Email = ?");
        $stmt->bind_param('s', $email);
    }

    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

//add new member, default pass is Library123
function addMember($fname, $lname, $email, $statusID, $adminID) {
    global $conn;

    if (memberEmailExists($email)) {
        return ['success' => false, 'message' => 'This email already exists'];
    }

    $defaultPass = password_hash('Library123', PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
        INSERT INTO members (FirstName, LastName, Email, Password, DefaultPassword, StatusID)
        VALUES (?, ?, ?, ?, 1, ?)
    ");
    $stmt->bind_param('ssssi', $fname, $lname, $email, $defaultPass, $statusID);

    if (!$stmt->execute()) {
        return ['success' => false, 'message' => $conn->error];
    }

    $newID = $conn->insert_id;
    logMemberAction($adminID, $newID, 'Created');

    return ['success' => true, 'message' => 'Member added. Default password is: Library123'];
}

//update member info
function updMember($memberID, $fname, $lname, $email, $statusID, $adminID) {
    global $conn;

    if (memberEmailExists($email, $memberID)) {
        return ['success' => false, 'message' => 'Email already used by another member'];
    }

    $stmt = $conn->prepare("
        UPDATE members SET FirstName=?, LastName=?, Email=?, StatusID=?
        WHERE MemberID=?
    ");
    $stmt->bind_param('sssii', $fname, $lname, $email, $statusID, $memberID);

    if (!$stmt->execute()) {
        return ['success' => false, 'message' => $conn->error];
    }

    logMemberAction($adminID, $memberID, 'Updated');
    return ['success' => true, 'message' => 'Member updated successfully'];
}

function searchMembers($q) {
    global $conn;

    $like = '%' . $conn->real_escape_string($q) . '%';

    $result = $conn->query("
        SELECT m.*, ms.StatusName
        FROM members m
        JOIN memberstatus ms ON m.StatusID = ms.StatusID
        WHERE m.MemberID LIKE '$like'
           OR m.FirstName LIKE '$like'
           OR m.LastName LIKE '$like'
           OR m.Email LIKE '$like'
           OR CONCAT(m.FirstName, ' ', m.LastName) LIKE '$like'
        ORDER BY m.MemberID DESC
    ");

    $data = [];
    while ($row = $result->fetch_assoc()) $data[] = $row;
    return $data;
}

function archiveMember($memberID, $adminID) {
    global $conn;
    $member = getMemberByID($memberID);
    if (!$member) {
        return ['success' => false, 'message' => 'Member not found'];
    }
    $conn->begin_transaction();
    try {
        $json = $conn->real_escape_string(json_encode($member));
        $conn->query("
            INSERT INTO archives (EntityType, EntityID, ArchivedData)
            VALUES ('Member', $memberID, '$json')
        ");
        logMemberAction($adminID, $memberID, 'Archived');

        $tablesWithMemberID = [
            'borrowrecords',  
            'borrowlogs',
            'ebookaccess',
            'borrowrequests',
            'donations', 
        ];
        foreach ($tablesWithMemberID as $table) {
            $stmt = $conn->prepare("DELETE FROM $table WHERE MemberID = ?");
            $stmt->bind_param('i', $memberID);
            $stmt->execute();
        }
        $stmt = $conn->prepare("DELETE FROM memberlogs WHERE AffectedMemberID = ?");
        $stmt->bind_param('i', $memberID);
        $stmt->execute();

        $stmt = $conn->prepare("DELETE FROM members WHERE MemberID = ?");
        $stmt->bind_param('i', $memberID);
        $stmt->execute();

        $conn->commit();
        return ['success' => true, 'message' => 'Member archived and deleted successfully'];
    } catch (Exception $e) {
        $conn->rollback();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}
function logMemberAction($adminID, $memberID, $action) {
    global $conn;

    $stmt = $conn->prepare("
        INSERT INTO memberlogs (StaffID, AffectedMemberID, Action)
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param('iis', $adminID, $memberID, $action);
    $stmt->execute();
}
?>