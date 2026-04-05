<?php

require_once __DIR__ . "/../../config/dbConn.php";

$conn = getConnection();

function borrowMontly() {
    global $conn;

    $res = $conn->query("
        SELECT DATE_FORMAT(BorrowDate, '%b %Y') AS Month,
            DATE_FORMAT(BorrowDate, '%Y-%m') AS MonthSort,
            COUNT(*) AS Total
        FROM borrowrecords
        WHERE BorrowDate >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
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
        SELECT m.Title, COUNT(br.RecordID) AS BorrowCount
        FROM borrowrecords br
        JOIN materials m ON br.MaterialID = m.MaterialID
        GROUP BY br.MaterialID, m.Title
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

    $members = $conn->query("SELECT COUNT(*) AS cnt FROM members")->fetch_assoc()['cnt'];
    $materials = $conn->query("SELECT COUNT(*) AS cnt FROM materials WHERE IsArchived = 0")->fetch_assoc()['cnt'];
    $active = $conn->query("SELECT COUNT(*) AS cnt FROM borrowrecords WHERE Status IN ('Borrowed','Overdue')")->fetch_assoc()['cnt'];
    $overdue = $conn->query("SELECT COUNT(*) AS cnt FROM borrowrecords WHERE Status = 'Overdue'")->fetch_assoc()['cnt'];

    return [
        'totalMembers' => $members,
        'totalMaterials' => $materials,
        'activeBorrows' => $active,
        'overdueCount' => $overdue
    ];
}