<?php

require_once __DIR__ . "/../../config/dbConn.php";
require_once __DIR__ . "/notificationOperations.php";
require_once __DIR__ . "/borrowOperations.php";

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

function approveReq($requestID, $staffID, $claimDeadline) {
    global $conn;

    $stmt = $conn->prepare("SELECT MemberID, MaterialID FROM borrowrequests WHERE RequestID=? AND Status='Pending'");
    $stmt->bind_param('i', $requestID);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!$row) return ['success' => false, 'message' => 'Request not found'];

    $memberID = $row['MemberID'];
    $materialID = $row['MaterialID'];

    $stmt = $conn->prepare("UPDATE borrowrequests SET Status='Approved', ProcessedBy=?, ProcessedDate=NOW(), ClaimDeadline=? WHERE RequestID=?");
    $stmt->bind_param('isi', $staffID, $claimDeadline, $requestID);
    $stmt->execute();

    $stmt = $conn->prepare("UPDATE materials SET AvailableQuantity = AvailableQuantity - 1 WHERE MaterialID=?");
    $stmt->bind_param('i', $materialID);
    $stmt->execute();

    $msg = "Your borrow request has been approved. Claim the book at the library by $claimDeadline.";
    $stmt = $conn->prepare("INSERT INTO notifications (UserID, UserType, Message) VALUES (?, 'Member', ?)");
    $stmt->bind_param('is', $memberID, $msg);
    $stmt->execute();

    $stmt = $conn->prepare("INSERT INTO borrowlogs (StaffID, MemberID, MaterialID, Action) VALUES (?, ?, ?, 'Approved')");
    $stmt->bind_param('iii', $staffID, $memberID, $materialID);
    $stmt->execute();

    return ['success' => true, 'message' => 'Request approved'];
}

function markClaimed($requestID, $staffID) {
    global $conn;

    $stmt = $conn->prepare("SELECT br.MemberID, br.MaterialID, m.Title
        FROM borrowrequests br
        JOIN materials m ON m.MaterialID = br.MaterialID
        WHERE br.RequestID=? AND br.Status='Approved' AND br.ClaimedAt IS NULL");
    $stmt->bind_param('i', $requestID);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if (!$row) return ['success' => false, 'message' => 'Already claimed or not found'];

    $memberID = $row['MemberID'];
    $materialID = $row['MaterialID'];
    $title = $row['Title'];

    $res = $conn->query("SELECT SettingValue FROM settings WHERE SettingKey='max_borrow_days'");
    $days = $res->fetch_assoc()['SettingValue'] ?? 14;
    $dueDate = date('Y-m-d', strtotime("+{$days} days"));

    $stmt = $conn->prepare("UPDATE borrowrequests SET ClaimedAt=NOW() WHERE RequestID=?");
    $stmt->bind_param('i', $requestID);
    $stmt->execute();

    $stmt = $conn->prepare("INSERT INTO borrowrecords (RequestID, MemberID, MaterialID, BorrowedBy, BorrowDate, DueDate, Status)
        VALUES (?, ?, ?, ?, NOW(), ?, 'Borrowed')");
    $stmt->bind_param('iiiis', $requestID, $memberID, $materialID, $staffID, $dueDate);
    $stmt->execute();

    $res = $conn->query("SELECT AvailableQuantity FROM materials WHERE MaterialID=$materialID");
    $qty = $res->fetch_assoc()['AvailableQuantity'];

    if ($qty <= 0) {
        $staffRes = $conn->query("SELECT s.StaffID FROM staffs s
            JOIN staffroles r ON r.RoleID = s.RoleID
            WHERE r.RoleName IN ('Admin','CirculationLibrarian') AND s.StatusID=1");

        while ($s = $staffRes->fetch_assoc()) {
            $notif = "\"$title\" is now out of stock.";
            $n = $conn->prepare("INSERT INTO notifications (UserID, UserType, Message) VALUES (?, 'Staff', ?)");
            $n->bind_param('is', $s['StaffID'], $notif);
            $n->execute();
        }
    }

    $msg = "You have claimed \"$title\". Due date: $dueDate. Enjoy!";
    $stmt = $conn->prepare("INSERT INTO notifications (UserID, UserType, Message) VALUES (?, 'Member', ?)");
    $stmt->bind_param('is', $memberID, $msg);
    $stmt->execute();

    $stmt = $conn->prepare("INSERT INTO borrowlogs (StaffID, MemberID, MaterialID, Action) VALUES (?, ?, ?, 'Claimed')");
    $stmt->bind_param('iii', $staffID, $memberID, $materialID);
    $stmt->execute();

    return ['success' => true, 'message' => "\"$title\" marked as claimed. {$days}-day borrow started."];
}

function markUnclaimed() {
    global $conn;

    $res = $conn->query("SELECT br.RequestID, br.MemberID, br.MaterialID, m.Title
        FROM borrowrequests br
        JOIN materials m ON m.MaterialID = br.MaterialID
        WHERE br.Status='Approved' AND br.ClaimedAt IS NULL AND br.ClaimDeadline < CURDATE()");

    while ($row = $res->fetch_assoc()) {
        $stmt = $conn->prepare("UPDATE borrowrequests SET Status='Cancelled' WHERE RequestID=?");
        $stmt->bind_param('i', $row['RequestID']);
        $stmt->execute();

        $stmt = $conn->prepare("UPDATE materials SET AvailableQuantity = AvailableQuantity + 1
            WHERE MaterialID=? AND AvailableQuantity < TotalQuantity");
        $stmt->bind_param('i', $row['MaterialID']);
        $stmt->execute();

        $msg = "Your claim for \"{$row['Title']}\" has expired and was automatically cancelled.";
        $stmt = $conn->prepare("INSERT INTO notifications (UserID, UserType, Message) VALUES (?, 'Member', ?)");
        $stmt->bind_param('is', $row['MemberID'], $msg);
        $stmt->execute();

        $stmt = $conn->prepare("INSERT INTO borrowlogs (MemberID, MaterialID, Action) VALUES (?, ?, 'Unclaimed')");
        $stmt->bind_param('ii', $row['MemberID'], $row['MaterialID']);
        $stmt->execute();
    }
}

function getApprovedClaims() {
    global $conn;

    $res = $conn->query("SELECT br.RequestID,
        CONCAT(m.FirstName, ' ', m.LastName) AS MemberName,
        mat.Title, mat.Author, mat.Description, mat.AvailableQuantity,
        mt.TypeName, br.ProcessedDate, br.ClaimDeadline
        FROM borrowrequests br
        JOIN members m ON m.MemberID = br.MemberID
        JOIN materials mat ON mat.MaterialID = br.MaterialID
        JOIN materialtypes mt ON mt.TypeID = mat.TypeID
        WHERE br.Status='Approved' AND br.ClaimedAt IS NULL AND mt.TypeID != 2
        ORDER BY br.ClaimDeadline ASC");

    $rows = [];
    while ($r = $res->fetch_assoc()) $rows[] = $r;
    return $rows;
}

function rejectReq($requestID, $staffID, $remarks = '') {
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

    if (!$req) return ['success' => false, 'message' => 'Request not found or already processed'];

    $stmt = $conn->prepare("
        UPDATE borrowrequests
        SET Status = 'Rejected', ProcessedBy = ?, ProcessedDate = NOW(), Remarks = ?
        WHERE RequestID = ?
    ");
    $stmt->bind_param('isi', $staffID, $remarks, $requestID);
    $stmt->execute();

    logCirculationAction($staffID, $req['MemberID'], $req['MaterialID'], 'Rejected');

    $msg = "Your borrow request for \"{$req['Title']}\" has been rejected.";
    if ($remarks) $msg .= " Reason: $remarks";

    createNotification($req['MemberID'], 'Member', $msg);

    return ['success' => true, 'message' => 'Request rejected'];
}

function logCirculationAction($staffID, $memberID, $materialID, $action) {
    global $conn;

    $stmt = $conn->prepare("INSERT INTO borrowlogs (StaffID, MemberID, MaterialID, Action) VALUES (?, ?, ?, ?)");
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

    if (!$record) return ['success' => false, 'message' => 'Record not found or already returned'];

    $fine = 0;
    $overdueDays = 0;
    $today = new DateTime();
    $due = new DateTime($record['DueDate']);

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

    $stmt = $conn->prepare("UPDATE materials SET AvailableQuantity = AvailableQuantity + 1 WHERE MaterialID = ?");
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
        'message' => 'Book returned' . ($fine > 0 ? '. Fine: ₱' . number_format($fine, 2) : ''),
        'fine' => $fine,
        'overdueDays' => $overdueDays,
        'rate' => $rate ?? 5.00
    ];
}


//TODO
//FIX ERROR in ts
function markLostDamaged($recordID, $staffID, $condition) {
    global $conn;

    if (!in_array($condition, ['Lost', 'Damaged'])) {
        return ['success' => false, 'message' => 'Invalid condition'];
    }

    $stmt = $conn->prepare("
        SELECT br.*, m.MaterialID, m.Title, m.ReplacementCost
        FROM borrowrecords br
        JOIN materials m ON br.MaterialID = m.MaterialID
        WHERE br.RecordID = ? AND br.Status IN ('Borrowed', 'Overdue')
    ");
    $stmt->bind_param('i', $recordID);
    $stmt->execute();
    $record = $stmt->get_result()->fetch_assoc();

    if (!$record) return ['success' => false, 'message' => 'Record not found or already processed'];

    $replacementCost = floatval($record['ReplacementCost'] ?? 0);

    $stmt = $conn->prepare("
        UPDATE borrowrecords
        SET Status = ?, ReturnDate = NOW(), OverdueFine = ?, ReturnProcessedBy = ?
        WHERE RecordID = ?
    ");
    $stmt->bind_param('sdii', $condition, $replacementCost, $staffID, $recordID);
    $stmt->execute();

    $stmt = $conn->prepare("
        UPDATE materials
        SET TotalQuantity = TotalQuantity - 1
        WHERE MaterialID = ? AND TotalQuantity > 0
    ");
    $stmt->bind_param('i', $record['MaterialID']);
    $stmt->execute();

    logCirculationAction($staffID, $record['MemberID'], $record['MaterialID'], $condition);

    $fineMsg = $replacementCost > 0 ? ' A replacement fine of ₱' . number_format($replacementCost, 2) . ' has been applied.' : '';
    $msg = '"' . $record['Title'] . '" has been marked as ' . strtolower($condition) . '.' . $fineMsg;
    createNotification($record['MemberID'], 'Member', $msg);

    return [
        'success' => true,
        'message' => 'Marked as ' . $condition . ($replacementCost > 0 ? '. Replacement fine: ₱' . number_format($replacementCost, 2) : ''),
        'fine' => $replacementCost
    ];
}