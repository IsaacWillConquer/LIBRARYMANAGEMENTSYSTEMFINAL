<?php

require_once __DIR__ . "/../../config/dbConn.php";

$conn = getConnection();

function getAvailMats() {
    global $conn;

    $result = $conn->query("
        SELECT m.*, mt.TypeName
        FROM materials m
        JOIN materialtypes mt ON m.TypeID = mt.TypeID
        WHERE m.IsArchived = 0 AND m.AvailableQuantity > 0
        ORDER BY m.DateAdded DESC
    ");

    $data = [];
    while ($row = $result->fetch_assoc()) $data[] = $row;
    return $data;
}

//popular borrowed within a month
function getTrend() {
    global $conn;

    $result = $conn->query("
        SELECT m.*, mt.TypeName, COUNT(br.RecordID) AS BorrowCount
        FROM materials m
        JOIN materialtypes mt ON m.TypeID = mt.TypeID
        LEFT JOIN borrowrecords br
            ON br.MaterialID = m.MaterialID
            AND br.BorrowDate >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        WHERE m.IsArchived = 0 AND m.AvailableQuantity > 0
        GROUP BY m.MaterialID
        ORDER BY BorrowCount DESC, m.DateAdded DESC
        LIMIT 6
    ");

    $data = [];
    while ($row = $result->fetch_assoc()) $data[] = $row;
    return $data;
}

function getRecs($memberID) {
    global $conn;

    $stmt = $conn->prepare("
        SELECT DISTINCT m.Genre
        FROM borrowrecords br
        JOIN materials m ON br.MaterialID = m.MaterialID
        WHERE br.MemberID = ?
        LIMIT 3
    ");
    $stmt->bind_param('i', $memberID);
    $stmt->execute();
    $result = $stmt->get_result();

    $genres = [];
    while ($row = $result->fetch_assoc()) $genres[] = $row;

    //no borrow history, just show latest
    if (empty($genres)) {
        $result = $conn->query("
            SELECT m.*, mt.TypeName
            FROM materials m
            JOIN materialtypes mt ON m.TypeID = mt.TypeID
            WHERE m.IsArchived = 0 AND m.AvailableQuantity > 0
            ORDER BY m.DateAdded DESC
            LIMIT 6
        ");
        $data = [];
        while ($row = $result->fetch_assoc()) $data[] = $row;
        return $data;
    }

    $genreValues = array_column($genres, 'Genre');
    $placeholders = implode(',', array_fill(0, count($genreValues), '?'));
    $types = str_repeat('s', count($genreValues));

    $stmt = $conn->prepare("
        SELECT m.*, mt.TypeName
        FROM materials m
        JOIN materialtypes mt ON m.TypeID = mt.TypeID
        WHERE m.IsArchived = 0
          AND m.AvailableQuantity > 0
          AND m.Genre IN ($placeholders)
        ORDER BY m.DateAdded DESC
        LIMIT 6
    ");
    $stmt->bind_param($types, ...$genreValues);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) $data[] = $row;
    return $data;
}

function getMyReq($memberID) {
    global $conn;

    $stmt = $conn->prepare("
        SELECT rq.*, m.Title, m.Author, mt.TypeName
        FROM borrowrequests rq
        JOIN materials m ON rq.MaterialID = m.MaterialID
        JOIN materialtypes mt ON m.TypeID = mt.TypeID
        WHERE rq.MemberID = ?
        ORDER BY rq.RequestDate DESC
        LIMIT 5
    ");
    $stmt->bind_param('i', $memberID);
    $stmt->execute();

    $data = [];
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) $data[] = $row;
    return $data;
}

function getMyActBorrows($memberID) {
    global $conn;

    $stmt = $conn->prepare("
        SELECT br.*, m.Title, m.Author, mt.TypeName
        FROM borrowrecords br
        JOIN materials m ON br.MaterialID = m.MaterialID
        JOIN materialtypes mt ON m.TypeID = mt.TypeID
        WHERE br.MemberID = ? AND br.Status IN ('Borrowed', 'Overdue')
        ORDER BY br.DueDate ASC
    ");
    $stmt->bind_param('i', $memberID);
    $stmt->execute();

    $data = [];
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) $data[] = $row;
    return $data;
}

function submitReq($memberID, $materialID) {
    global $conn;

    //check if member is active
    $stmt = $conn->prepare("SELECT StatusID FROM members WHERE MemberID = ?");
    $stmt->bind_param('i', $memberID);
    $stmt->execute();
    $member = $stmt->get_result()->fetch_assoc();

    if (!$member || $member['StatusID'] != 1) {
        return ['success' => false, 'message' => 'Your account is not active'];
    }

    $stmt = $conn->prepare("SELECT AvailableQuantity, IsArchived, Title FROM materials WHERE MaterialID = ?");
    $stmt->bind_param('i', $materialID);
    $stmt->execute();
    $material = $stmt->get_result()->fetch_assoc();

    if (!$material || $material['IsArchived']) {
        return ['success' => false, 'message' => 'Material not found'];
    }
    if ($material['AvailableQuantity'] < 1) {
        return ['success' => false, 'message' => 'No copies available'];
    }

    //check for existing pending request
    $stmt = $conn->prepare("
        SELECT RequestID FROM borrowrequests
        WHERE MemberID = ? AND MaterialID = ? AND Status = 'Pending'
        LIMIT 1
    ");
    $stmt->bind_param('ii', $memberID, $materialID);
    $stmt->execute();

    if ($stmt->get_result()->fetch_assoc()) {
        return ['success' => false, 'message' => 'You already have a pending or active request for this material'];
    }

    $limitRow = $conn->query("SELECT SettingValue FROM settings WHERE SettingKey = 'max_borrow_limit'")->fetch_assoc();
    $limit = $limitRow ? intval($limitRow['SettingValue']) : 3;

    $stmt = $conn->prepare("
        SELECT COUNT(*) AS cnt
        FROM borrowrecords
        WHERE MemberID = ? AND Status IN ('Borrowed', 'Overdue')
    ");
    $stmt->bind_param('i', $memberID);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if ($row['cnt'] >= $limit) {
        return ['success' => false, 'message' => "You have reached the borrow limit ($limit items)"];
    }

    $stmt = $conn->prepare("
        INSERT INTO borrowrequests (MemberID, MaterialID, Status)
        VALUES (?, ?, 'Pending')
    ");
    $stmt->bind_param('ii', $memberID, $materialID);

    if (!$stmt->execute()) return ['success' => false, 'message' => $conn->error];

    logBorrowAction(null, $memberID, $materialID, 'Requested');

    return ['success' => true, 'message' => 'Borrow request submitted. Please wait for approval.'];
}

function cancelReq($memberID, $requestID) {
    global $conn;

    $stmt = $conn->prepare("
        SELECT MaterialID FROM borrowrequests
        WHERE RequestID = ? AND MemberID = ? AND Status = 'Pending'
    ");
    $stmt->bind_param('ii', $requestID, $memberID);
    $stmt->execute();
    $req = $stmt->get_result()->fetch_assoc();

    if (!$req) {
        return ['success' => false, 'message' => 'Request not found or cannot be cancelled'];
    }

    $stmt = $conn->prepare("UPDATE borrowrequests SET Status = 'Cancelled' WHERE RequestID = ?");
    $stmt->bind_param('i', $requestID);

    if (!$stmt->execute()) return ['success' => false, 'message' => $conn->error];

    logBorrowAction(null, $memberID, $req['MaterialID'], 'Cancelled');

    return ['success' => true, 'message' => 'Request cancelled'];
}

function getBorrowHist($memberID) {
    global $conn;

    $stmt = $conn->prepare("
        SELECT br.*, m.Title, m.Author, mt.TypeName
        FROM borrowrecords br
        JOIN materials m ON br.MaterialID = m.MaterialID
        JOIN materialtypes mt ON m.TypeID = mt.TypeID
        WHERE br.MemberID = ?
        ORDER BY br.BorrowDate DESC
    ");
    $stmt->bind_param('i', $memberID);
    $stmt->execute();

    $data = [];
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) $data[] = $row;
    return $data;
}

function logBorrowAction($staffID, $memberID, $materialID, $action) {
    global $conn;

    $stmt = $conn->prepare("
        INSERT INTO borrowlogs (StaffID, MemberID, MaterialID, Action)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param('iiis', $staffID, $memberID, $materialID, $action);
    $stmt->execute();
}