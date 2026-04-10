<?php

require_once __DIR__ . "/../../config/dbConn.php";

$conn = getConnection();


function getAvailMats() {
    global $conn;

    $result = $conn->query("
        SELECT m.*, mt.TypeName
        FROM materials m
        JOIN materialtypes mt ON m.TypeID = mt.TypeID
        WHERE m.IsArchived = 0
        AND (m.AvailableQuantity > 0 OR m.TypeID = 2)
        ORDER BY m.DateAdded DESC
    ");

    $data = [];
    while ($row = $result->fetch_assoc()) $data[] = $row;
    return $data;
}

function getTrend() {
    global $conn;

    $result = $conn->query("
        SELECT m.*, mt.TypeName, COUNT(br.RecordID) AS BorrowCount
        FROM materials m
        JOIN materialtypes mt ON m.TypeID = mt.TypeID
        LEFT JOIN borrowrecords br
            ON br.MaterialID = m.MaterialID
            AND br.BorrowDate >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        WHERE m.IsArchived = 0
        AND (m.AvailableQuantity > 0 OR m.TypeID = 2)
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

    if (empty($genres)) {
        $result = $conn->query("
            SELECT m.*, mt.TypeName
            FROM materials m
            JOIN materialtypes mt ON m.TypeID = mt.TypeID
            WHERE m.IsArchived = 0 AND (m.AvailableQuantity > 0 OR m.TypeID = 2)
            ORDER BY m.DateAdded DESC
            LIMIT 6
        ");
        $data = [];
        while ($row = $result->fetch_assoc()) $data[] = $row;
        return $data;
    }

    $genreValues  = array_column($genres, 'Genre');
    $placeholders = implode(',', array_fill(0, count($genreValues), '?'));
    $types = str_repeat('s', count($genreValues));

    $stmt = $conn->prepare("
        SELECT m.*, mt.TypeName
        FROM materials m
        JOIN materialtypes mt ON m.TypeID = mt.TypeID
        WHERE m.IsArchived = 0
          AND (m.AvailableQuantity > 0 OR m.TypeID = 2)
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

    $data   = [];
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

    $data   = [];
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) $data[] = $row;
    return $data;
}

function submitReq($memberID, $materialID) {
    global $conn;

    $stmt = $conn->prepare("SELECT StatusID FROM members WHERE MemberID = ?");
    $stmt->bind_param('i', $memberID);
    $stmt->execute();
    $member = $stmt->get_result()->fetch_assoc();

    if (!$member || $member['StatusID'] != 1) {
        return ['success' => false, 'message' => 'Your account is not active'];
    }

    $stmt = $conn->prepare("SELECT TypeID, IsArchived, AvailableQuantity FROM materials WHERE MaterialID = ?");
    $stmt->bind_param('i', $materialID);
    $stmt->execute();
    $material = $stmt->get_result()->fetch_assoc();

    if (!$material || $material['IsArchived']) {
        return ['success' => false, 'message' => 'Material not found'];
    }

    // ebook - just return ok, JS opens reader modal
    if ($material['TypeID'] == 2) {
        return ['success' => true, 'ebook' => true];
    }

    if ($material['AvailableQuantity'] < 1) {
        return ['success' => false, 'message' => 'No copies available'];
    }

    $stmt = $conn->prepare("
        SELECT RequestID FROM borrowrequests
        WHERE MemberID = ? AND MaterialID = ? AND Status = 'Pending'
        LIMIT 1
    ");
    $stmt->bind_param('ii', $memberID, $materialID);
    $stmt->execute();

    if ($stmt->get_result()->fetch_assoc()) {
        return ['success' => false, 'message' => 'You already have a pending request for this material'];
    }

    $limitRow = $conn->query("SELECT SettingValue FROM settings WHERE SettingKey = 'max_borrow_limit'")->fetch_assoc();
    $limit    = $limitRow ? intval($limitRow['SettingValue']) : 3;

    $stmt = $conn->prepare("
        SELECT COUNT(*) AS cnt FROM borrowrecords
        WHERE MemberID = ? AND Status IN ('Borrowed', 'Overdue')
    ");
    $stmt->bind_param('i', $memberID);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if ($row['cnt'] >= $limit) {
        return ['success' => false, 'message' => "You have reached the borrow limit ($limit items)"];
    }

    $stmt = $conn->prepare("INSERT INTO borrowrequests (MemberID, MaterialID, Status) VALUES (?, ?, 'Pending')");
    $stmt->bind_param('ii', $memberID, $materialID);

    if (!$stmt->execute()) return ['success' => false, 'message' => $conn->error];

    logBorrowAction(null, $memberID, $materialID, 'Requested');

    return ['success' => true, 'message' => 'Borrow request submitted. Please wait for approval.'];
}

function accessEbook($memberID, $materialID) {
    global $conn;

    $stmt = $conn->prepare("SELECT MaterialID, Title, Author, Description, Genre, FilePath FROM materials WHERE MaterialID=? AND TypeID=2 AND IsArchived=0");    $stmt->bind_param('i', $materialID);
    $stmt->execute();
    $mat = $stmt->get_result()->fetch_assoc();
    if (!$mat) return ['success' => false, 'message' => 'EBook not found'];

    $stmt = $conn->prepare("
        INSERT INTO ebookaccess (MemberID, MaterialID, AccessCount, LastAccessed)
        VALUES (?, ?, 1, NOW())
        ON DUPLICATE KEY UPDATE AccessCount = AccessCount + 1, LastAccessed = NOW()
    ");
    $stmt->bind_param('ii', $memberID, $materialID);
    $stmt->execute();

    return ['success' => true, 'material' => $mat];
}

function getCurrentlyReading($memberID) {
    global $conn;

    $stmt = $conn->prepare("
        SELECT m.MaterialID, m.Title, m.Author, m.Genre,
               ea.AccessCount, ea.LastAccessed
        FROM ebookaccess ea
        JOIN materials m ON m.MaterialID = ea.MaterialID
        WHERE ea.MemberID = ?
        ORDER BY ea.LastAccessed DESC
        LIMIT 3
    ");
    $stmt->bind_param('i', $memberID);
    $stmt->execute();

    $data= [];
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) $data[] = $row;
    return $data;
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

    if (!$req) return ['success' => false, 'message' => 'Request not found or cannot be cancelled'];

    $stmt = $conn->prepare("UPDATE borrowrequests SET Status = 'Cancelled' WHERE RequestID = ?");
    $stmt->bind_param('i', $requestID);

    if (!$stmt->execute()) return ['success' => false, 'message' => $conn->error];

    logBorrowAction(null, $memberID, $req['MaterialID'], 'Cancelled');

    return ['success' => true, 'message' => 'Request cancelled'];
}

function cancelPendingClaim($requestID, $memberID) {
    global $conn;

    $stmt = $conn->prepare("
        SELECT MaterialID FROM borrowrequests
        WHERE RequestID = ? AND MemberID = ? AND Status = 'Approved' AND ClaimedAt IS NULL
    ");
    $stmt->bind_param('ii', $requestID, $memberID);
    $stmt->execute();
    $req = $stmt->get_result()->fetch_assoc();

    if (!$req) return ['success' => false, 'message' => 'Claim not found or cannot be cancelled'];

    $stmt = $conn->prepare("UPDATE borrowrequests SET Status = 'Cancelled' WHERE RequestID = ?");
    $stmt->bind_param('i', $requestID);
    if (!$stmt->execute()) return ['success' => false, 'message' => $conn->error];

    $stmt = $conn->prepare("UPDATE materials SET AvailableQuantity = AvailableQuantity + 1 WHERE MaterialID = ?");
    $stmt->bind_param('i', $req['MaterialID']);
    $stmt->execute();

    logBorrowAction(null, $memberID, $req['MaterialID'], 'Cancelled');

    return ['success' => true, 'message' => 'Claim cancelled. The book has been returned to stock.'];
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

    $data   = [];
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) $data[] = $row;
    return $data;
}

function getMemberPendingClaims($memberID) {
    global $conn;

    $stmt = $conn->prepare("
        SELECT rq.RequestID, rq.ProcessedDate, rq.ClaimDeadline,
               m.Title, m.Author, m.AvailableQuantity, m.Description,
               mt.TypeName
        FROM borrowrequests rq
        JOIN materials m ON rq.MaterialID = m.MaterialID
        JOIN materialtypes mt ON m.TypeID = mt.TypeID
        WHERE rq.MemberID = ?
          AND rq.Status = 'Approved'
          AND rq.ClaimedAt IS NULL
          AND mt.TypeID != 2
        ORDER BY rq.ClaimDeadline ASC
    ");
    $stmt->bind_param('i', $memberID);
    $stmt->execute();

    $data   = [];
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) $data[] = $row;
    return $data;
}

function submitDonation($memberID, $title, $author, $genre, $description, $condition) {
    global $conn;

    if (!$title || !$author) return ['success' => false, 'message' => 'Title and author are required'];

    $stmt = $conn->prepare("
        INSERT INTO donations (MemberID, Title, Author, Genre, Description, BookCondition)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param('isssss', $memberID, $title, $author, $genre, $description, $condition);

    if (!$stmt->execute()) return ['success' => false, 'message' => $conn->error];

    return ['success' => true, 'message' => 'Donation submitted! The librarian will review your request.'];
}

function getPendingDonations() {
    global $conn;

    $res = $conn->query("
        SELECT d.*, CONCAT(m.FirstName, ' ', m.LastName) AS MemberName
        FROM donations d
        JOIN members m ON m.MemberID = d.MemberID
        WHERE d.Status = 'Pending'
        ORDER BY d.DateSubmitted ASC
    ");

    $rows = [];
    while ($r = $res->fetch_assoc()) $rows[] = $r;
    return $rows;
}

function reviewDonation($donationID, $staffID, $status) {
    global $conn;

    $stmt = $conn->prepare("SELECT MemberID, Title FROM donations WHERE DonationID=? AND Status='Pending'");
    $stmt->bind_param('i', $donationID);
    $stmt->execute();
    $don = $stmt->get_result()->fetch_assoc();
    if (!$don) return ['success' => false, 'message' => 'Donation not found'];

    $stmt = $conn->prepare("UPDATE donations SET Status=?, ReviewedBy=?, ReviewedDate=NOW() WHERE DonationID=?");
    $stmt->bind_param('sii', $status, $staffID, $donationID);
    $stmt->execute();

    $msg = $status === 'Accepted'
        ? "Your donation of \"{$don['Title']}\" has been accepted. Thank you!"
        : "Your donation of \"{$don['Title']}\" was not accepted at this time.";

    $stmt = $conn->prepare("INSERT INTO notifications (UserID, UserType, Message) VALUES (?, 'Member', ?)");
    $stmt->bind_param('is', $don['MemberID'], $msg);
    $stmt->execute();

    return ['success' => true, 'message' => "Donation $status"];
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

function searchAvailMats($q, $type = '') {
    global $conn;

    $like       = '%' . $conn->real_escape_string($q) . '%';
    $typeFilter = '';
    if ($type && in_array($type, ['Book', 'EBook', 'Journal'])) {
        $typeFilter = "AND mt.TypeName = '" . $conn->real_escape_string($type) . "'";
    }

    $result = $conn->query("
        SELECT m.*, mt.TypeName
        FROM materials m
        JOIN materialtypes mt ON m.TypeID = mt.TypeID
        WHERE m.IsArchived = 0
          AND (m.AvailableQuantity > 0 OR m.TypeID = 2)
          $typeFilter
          AND (m.Title LIKE '$like' OR m.Author LIKE '$like' OR m.Genre LIKE '$like')
        ORDER BY m.DateAdded DESC
    ");

    $data = [];
    while ($row = $result->fetch_assoc()) $data[] = $row;
    return $data;
}