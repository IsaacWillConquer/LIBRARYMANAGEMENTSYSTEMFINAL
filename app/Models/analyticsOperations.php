<?php

require_once __DIR__ . "/../../config/dbConn.php";

$conn = getConnection();


//Change to weekly to lazy to refactor
function borrowMontly() {
    global $conn;


    //7 days
    $res = $conn->query("
        SELECT DATE_FORMAT(BorrowDate, '%b %d') AS Month,
               DATE(BorrowDate) AS MonthSort,
               COUNT(*) AS Total
        FROM borrowrecords
        
        WHERE BorrowDate >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
        GROUP BY MonthSort, Month
        ORDER BY MonthSort ASC
    ");

    $data = [];
    while ($row = $res->fetch_assoc()) $data[] = $row;
    return $data;
}

function topBorrow() {
    global $conn;

    $res = $conn->query("
        SELECT m.Title, mt.TypeName, COUNT(br.RecordID) AS BorrowCount
        FROM borrowrecords br
        JOIN materials m ON br.MaterialID = m.MaterialID
        JOIN materialtypes mt ON mt.TypeID = m.TypeID
        GROUP BY br.MaterialID, m.Title, mt.TypeName
        ORDER BY BorrowCount DESC
        LIMIT 5
    ");

    $data = [];
    while ($row = $res->fetch_assoc()) $data[] = $row;
    return $data;
}

function borrowStats() {
    global $conn;

    $result = $conn->query("
        SELECT Status, COUNT(*) AS Total
        FROM borrowrecords
        GROUP BY Status
    ");

    $data = [];
    while ($row = $result->fetch_assoc()) $data[] = $row;
    return $data;
}

function matType() {
    global $conn;

    $result = $conn->query("
        SELECT mt.TypeName, COUNT(*) AS Total
        FROM materials m
        JOIN materialtypes mt ON m.TypeID = mt.TypeID
        WHERE m.IsArchived = 0
        GROUP BY mt.TypeName
    ");

    $data = [];
    while ($row = $result->fetch_assoc()) $data[] = $row;
    return $data;
}

function summaryStats() {
    global $conn;

    $stats = $conn->query("
        SELECT
            (SELECT COUNT(*) FROM members) AS members,
            (SELECT COUNT(*) FROM materials WHERE IsArchived = 0) AS materials,
            (SELECT COUNT(*) FROM borrowrecords WHERE Status IN ('Borrowed','Overdue')) AS active,
            (SELECT COUNT(*) FROM borrowrecords WHERE Status = 'Overdue') AS overdue,
            (SELECT COALESCE(SUM(OverdueFine), 0) FROM borrowrecords WHERE OverdueFine > 0) AS fines,
            (SELECT COUNT(*) FROM borrowrequests WHERE Status = 'Pending') AS pending,
            (SELECT COUNT(*) FROM donations WHERE Status = 'Pending') AS donations
    ")->fetch_assoc();

    return [
        'totalMembers' => $stats['members'],
        'totalMaterials' => $stats['materials'],
        'activeBorrows' => $stats['active'],
        'overdueCount' => $stats['overdue'],
        'totalFines' => number_format($stats['fines'], 2),
        'pendingReqs' => $stats['pending'],
        'pendingDonations' => $stats['donations']
    ];
}
function genreStats() {
    global $conn;

    $res = $conn->query("
        SELECT m.Genre, COUNT(br.RecordID) AS BorrowCount
        FROM borrowrecords br
        JOIN materials m ON br.MaterialID = m.MaterialID
        WHERE m.Genre IS NOT NULL AND m.Genre != ''
        GROUP BY m.Genre
        ORDER BY BorrowCount DESC
        LIMIT 6
    ");

    $data = [];
    while ($row = $res->fetch_assoc()) $data[] = $row;
    return $data;
}

function topMembers() {
    global $conn;

    $res = $conn->query("
        SELECT CONCAT(m.FirstName, ' ', m.LastName) AS MemberName,
               COUNT(br.RecordID) AS BorrowCount
        FROM borrowrecords br
        JOIN members m ON m.MemberID = br.MemberID
        GROUP BY br.MemberID, MemberName
        ORDER BY BorrowCount DESC
        LIMIT 5
    ");

    $data = [];
    while ($row = $res->fetch_assoc()) $data[] = $row;
    return $data;
}

function recentActivity() {
    global $conn;

    $res = $conn->query("
        SELECT bl.Action, bl.LogTime,
               CONCAT(m.FirstName, ' ', m.LastName) AS MemberName,
               mat.Title
        FROM borrowlogs bl
        JOIN members m ON m.MemberID = bl.MemberID
        JOIN materials mat ON mat.MaterialID = bl.MaterialID
        ORDER BY bl.LogTime DESC
        LIMIT 10
    ");

    $data = [];
    while ($row = $res->fetch_assoc()) $data[] = $row;
    return $data;
}