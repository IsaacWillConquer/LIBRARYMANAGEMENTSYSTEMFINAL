<?php

require_once __DIR__ . "/../../config/dbConn.php";
require_once __DIR__ . "/notificationOperations.php";

$conn = getConnection();

function getPendingReq() {
    global $conn;

    $result = $conn->query("
        SELECT rq.*, m.Title, m.Author, mt.TypeName,
            CONCAT(mb.FirstName, ' ', mb.LastName) AS MemberName
        FROM borrowrequests rq
        JOIN materials m ON rq.MaterialID = m.MaterialID
        JOIN materialtypes mt ON m.TypeID = mt.TypeID
        JOIN members mb ON rq.MemberID = mb.MemberID
        WHERE rq.Status = 'Pending'
        ORDER BY rq.RequestDate ASC
    ");

    $data = [];
    while ($row = $result->fetch_assoc()) $data[] = $row;
    return $data;
}

function approveReq($requestID, $staffID) {
    global $conn;

    $stmt = $conn->prepare("
        SELECT rq.MemberID, rq.MaterialID, m.AvailableQuantity, m.Title
        FROM borrowrequests rq
        JOIN materials m ON rq.MaterialID = m.MaterialID
        WHERE rq.RequestID = ? AND rq.Status = 'Pending'
    ");
    $stmt->bind_param('i', $requestID);
    $stmt->execute();
    $req = $stmt->get_result()->fetch_assoc();

    if (!$req) {
        return ['success' => false, 'message' => 'Request not found or already processed'];
    }
    if ($req['AvailableQuantity'] < 1) {
        return ['success' => false, 'message' => 'No copies available'];
    }

    $stmt = $conn->prepare("
        UPDATE borrowrequests
        SET Status = 'Approved', ProcessedBy = ?, ProcessedDate = NOW()
        WHERE RequestID = ?
    ");
    $stmt->bind_param('ii', $staffID, $requestID);
    $stmt->execute();

    $stmt = $conn->prepare("
        INSERT INTO borrowrecords (RequestID, MemberID, MaterialID, BorrowedBy, BorrowDate, DueDate, Status)
        VALUES (?, ?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 14 DAY), 'Borrowed')
    ");
    $stmt->bind_param('iiii', $requestID, $req['MemberID'], $req['MaterialID'], $staffID);
    $stmt->execute();

    $stmt = $conn->prepare("
        UPDATE materials SET AvailableQuantity = AvailableQuantity - 1 WHERE MaterialID = ?
    ");
    $stmt->bind_param('i', $req['MaterialID']);
    $stmt->execute();

    logCirculationAction($staffID, $req['MemberID'], $req['MaterialID'], 'Approved');

    return ['success' => true, 'message' => 'Request approved'];
}

function rejectReq($requestID, $staffID) {
    global $conn;

    $stmt = $conn->prepare("
        SELECT rq.MemberID, rq.MaterialID, m.Title
        FROM borrowrequests rq
        JOIN materials m ON rq.MaterialID = m.MaterialID
        WHERE rq.RequestID = ? AND rq.Status = 'Pending'
    ");
    $stmt->bind_param('i', $requestID);
    $stmt->execute();
    $req = $stmt->get_result()->fetch_assoc();

    if (!$req) {
        return ['success' => false, 'message' => 'Request not found or already processed'];
    }

    $stmt = $conn->prepare("
        UPDATE borrowrequests
        SET Status = 'Rejected', ProcessedBy = ?, ProcessedDate = NOW()
        WHERE RequestID = ?
    ");
    $stmt->bind_param('ii', $staffID, $requestID);
    $stmt->execute();

    logCirculationAction($staffID, $req['MemberID'], $req['MaterialID'], 'Rejected');
    createNotification($req['MemberID'], 'Member', 'Your borrow request for "' . $req['Title'] . '" has been rejected. Please contact the library for more information.');

    return ['success' => true, 'message' => 'Request rejected'];
}

function logCirculationAction($staffID, $memberID, $materialID, $action) {
    global $conn;

    $stmt = $conn->prepare("
        INSERT INTO borrowlogs (StaffID, MemberID, MaterialID, Action)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param('iiis', $staffID, $memberID, $materialID, $action);
    $stmt->execute();
}

function getActiveBorrow() {
    global $conn;

    $res = $conn->query("
        SELECT br.*, m.Title, m.Author, mt.TypeName,
               CONCAT(mb.FirstName, ' ', mb.LastName) AS MemberName
        FROM borrowrecords br
        JOIN materials m ON br.MaterialID = m.MaterialID
        JOIN materialtypes mt ON m.TypeID = mt.TypeID
        JOIN members mb ON br.MemberID = mb.MemberID
        WHERE br.Status IN ('Borrowed', 'Overdue')
        ORDER BY br.DueDate ASC
    ");

    $data = [];
    while ($row = $res->fetch_assoc()) $data[] = $row;
    return $data;
}

function markOverdue() {
    global $conn;

    $result = $conn->query("
        SELECT RecordID, MemberID, MaterialID FROM borrowrecords
        WHERE Status = 'Borrowed' AND DueDate < NOW()
    ");

    while ($row = $result->fetch_assoc()) {
        $stmt = $conn->prepare("UPDATE borrowrecords SET Status = 'Overdue' WHERE RecordID = ?");
        $stmt->bind_param('i', $row['RecordID']);
        $stmt->execute();

        logCirculationAction(null, $row['MemberID'], $row['MaterialID'], 'MarkedOverdue');
        createNotification($row['MemberID'], 'Member', 'Your borrowed material is now overdue. A fine of ₱5/day is being accumulated.');
    }
}

function returnBook($recordID, $staffID) {
    global $conn;

    $stmt = $conn->prepare("
        SELECT br.*, m.MaterialID, m.Title
        FROM borrowrecords br
        JOIN materials m ON br.MaterialID = m.MaterialID
        WHERE br.RecordID = ? AND br.Status IN ('Borrowed', 'Overdue')
    ");
    $stmt->bind_param('i', $recordID);
    $stmt->execute();
    $record = $stmt->get_result()->fetch_assoc();

    if (!$record) {
        return ['success' => false, 'message' => 'Record not found or already returned'];
    }

    $fine = 0;
    $overdueDays = 0;
    $today = new DateTime();
    $due = new DateTime($record['DueDate']);

    //5 pesos per day we ffinna ball!!!!!!!!!!!!
    if ($today > $due) {
        $overdueDays = $today->diff($due)->days;
        $rateRow = $conn->query("SELECT SettingValue FROM settings WHERE SettingKey = 'overdue_rate_per_day'")->fetch_assoc();
        $rate = $rateRow ? floatval($rateRow['SettingValue']) : 5.00;
        $fine = $overdueDays * $rate;
    } else {
        $rate = 5.00;
    }

    $stmt = $conn->prepare("
        UPDATE borrowrecords
        SET Status = 'Returned', ReturnDate = NOW(), OverdueFine = ?, ReturnProcessedBy = ?
        WHERE RecordID = ?
    ");
    $stmt->bind_param('dii', $fine, $staffID, $recordID);
    $stmt->execute();

    $stmt = $conn->prepare("
        UPDATE materials SET AvailableQuantity = AvailableQuantity + 1 WHERE MaterialID = ?
    ");
    $stmt->bind_param('i', $record['MaterialID']);
    $stmt->execute();

    logCirculationAction($staffID, $record['MemberID'], $record['MaterialID'], 'Returned');

    $message = '"' . $record['Title'] . '" has been returned.';
    if ($fine > 0) {
        $message .= ' Overdue by ' . $overdueDays . ' day(s) × ₱' . $rate . '/day = ₱' . number_format($fine, 2) . '.';
    }

    createNotification($record['MemberID'], 'Member', $message);

    return [
        'success' => true,
        'message' => 'Book returned' . ($fine > 0 ? '. Overdue by ' . $overdueDays . ' day(s) × ₱' . $rate . '/day = ₱' . number_format($fine, 2) : ''),
        'fine' => $fine,
        'overdueDays' => $overdueDays,
        'rate' => $rate ?? 5.00
    ];
}