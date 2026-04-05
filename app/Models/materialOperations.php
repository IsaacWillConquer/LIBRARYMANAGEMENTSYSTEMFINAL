<?php

require_once __DIR__ . "/../../config/dbConn.php";

$conn = getConnection();

function getAllMaterials() {
    global $conn;

    $result = $conn->query("
        SELECT m.*, mt.TypeName
        FROM materials m
        JOIN materialtypes mt ON m.TypeID = mt.TypeID
        WHERE m.IsArchived = 0
        ORDER BY m.MaterialID DESC
    ");

    $data = [];
    while ($row = $result->fetch_assoc()) $data[] = $row;
    return $data;
}

function getMaterialTypes() {
    global $conn;

    $result = $conn->query("SELECT * FROM materialtypes");
    $data = [];
    while ($row = $result->fetch_assoc()) $data[] = $row;
    return $data;
}

function getMaterialByID($id) {
    global $conn;

    $stmt = $conn->prepare("
        SELECT m.*, mt.TypeName
        FROM materials m
        JOIN materialtypes mt ON m.TypeID = mt.TypeID
        WHERE m.MaterialID = ?
    ");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getBooks() {
    global $conn;
    $result = $conn->query("SELECT * FROM materials WHERE TypeID = 1 AND IsArchived = 0 ORDER BY MaterialID DESC");
    $data = [];
    while ($row = $result->fetch_assoc()) $data[] = $row;
    return $data;
}

function getEbooks() {
    global $conn;
    $result = $conn->query("SELECT * FROM materials WHERE TypeID = 2 AND IsArchived = 0 ORDER BY MaterialID DESC");
    $data = [];
    while ($row = $result->fetch_assoc()) $data[] = $row;
    return $data;
}

function getJournals() {
    global $conn;
    $result = $conn->query("SELECT * FROM materials WHERE TypeID = 3 AND IsArchived = 0 ORDER BY MaterialID DESC");
    $data = [];
    while ($row = $result->fetch_assoc()) $data[] = $row;
    return $data;
}

function addBook($data, $staffID) {
    global $conn;

    $stmt = $conn->prepare("
        INSERT INTO materials (TypeID, Title, Author, ISBN, Description, Genre, PublishDate, Publisher, TotalQuantity, AvailableQuantity, AddedBy)
        VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        'sssssssiii',
        $data['title'], $data['author'], $data['isbn'],
        $data['description'], $data['genre'], $data['publishDate'],
        $data['publisher'], $data['totalQty'], $data['totalQty'], $staffID
    );

    if (!$stmt->execute()) {
        return ['success' => false, 'message' => $conn->error];
    }

    logMaterialAction($staffID, $conn->insert_id, 'Added');
    return ['success' => true, 'message' => 'Book added successfully'];
}

function addEbook($data, $staffID) {
    global $conn;

    $stmt = $conn->prepare("
        INSERT INTO materials (TypeID, Title, Author, ISBN, Description, Genre, PublishDate, Publisher, TotalQuantity, AvailableQuantity, EbookFormat, AccessStart, AccessEnd, AddedBy)
        VALUES (2, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        'sssssssiiissi',
        $data['title'], $data['author'], $data['isbn'],
        $data['description'], $data['genre'], $data['publishDate'],
        $data['publisher'], $data['totalQty'], $data['totalQty'],
        $data['ebookFormat'], $data['accessStart'], $data['accessEnd'], $staffID
    );

    if (!$stmt->execute()) {
        return ['success' => false, 'message' => $conn->error];
    }

    logMaterialAction($staffID, $conn->insert_id, 'Added');
    return ['success' => true, 'message' => 'EBook added successfully'];
}

function addJournal($data, $staffID) {
    global $conn;

    $stmt = $conn->prepare("
        INSERT INTO materials (TypeID, Title, Author, ISBN, Description, Genre, PublishDate, Publisher, TotalQuantity, AvailableQuantity, JournalType, JournalInterval, AddedBy)
        VALUES (3, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        'sssssssiissi',
        $data['title'], $data['author'], $data['isbn'],
        $data['description'], $data['genre'], $data['publishDate'],
        $data['publisher'], $data['totalQty'], $data['totalQty'],
        $data['journalType'], $data['journalInterval'], $staffID
    );

    if (!$stmt->execute()) {
        return ['success' => false, 'message' => $conn->error];
    }

    logMaterialAction($staffID, $conn->insert_id, 'Added');
    return ['success' => true, 'message' => 'Journal added successfully'];
}

function updMaterial($data, $staffID) {
    global $conn;

    $stmt = $conn->prepare("
        UPDATE materials
        SET Title=?, Author=?, ISBN=?, Description=?, Genre=?,
            PublishDate=?, Publisher=?, TotalQuantity=?, AvailableQuantity=?,
            EbookFormat=?, AccessStart=?, AccessEnd=?,
            JournalType=?, JournalInterval=?
        WHERE MaterialID=?
    ");
    $stmt->bind_param(
        'sssssssiisisssi',
        $data['title'], $data['author'], $data['isbn'],
        $data['description'], $data['genre'], $data['publishDate'],
        $data['publisher'], $data['totalQty'], $data['availableQty'],
        $data['ebookFormat'], $data['accessStart'], $data['accessEnd'],
        $data['journalType'], $data['journalInterval'], $data['materialID']
    );

    if (!$stmt->execute()) {
        return ['success' => false, 'message' => $conn->error];
    }

    logMaterialAction($staffID, $data['materialID'], 'Updated');
    return ['success' => true, 'message' => 'Material updated successfully'];
}

function archiveMaterial($materialID, $staffID) {
    global $conn;

    $stmt = $conn->prepare("
        UPDATE materials SET IsArchived=1, ArchivedDate=NOW()
        WHERE MaterialID=?
    ");
    $stmt->bind_param('i', $materialID);

    if (!$stmt->execute()) {
        return ['success' => false, 'message' => $conn->error];
    }

    logMaterialAction($staffID, $materialID, 'Archived');
    return ['success' => true, 'message' => 'Material archived successfully'];
}

function logMaterialAction($staffID, $materialID, $action) {
    global $conn;

    $stmt = $conn->prepare("
        INSERT INTO materiallogs (StaffID, MaterialID, Action)
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param('iis', $staffID, $materialID, $action);
    $stmt->execute();
}

function searchMaterials($q) {
    global $conn;

    $like = '%' . $conn->real_escape_string($q) . '%';

    $result = $conn->query("
        SELECT m.*, mt.TypeName
        FROM materials m
        JOIN materialtypes mt ON m.TypeID = mt.TypeID
        WHERE m.IsArchived = 0
          AND (
              m.Title LIKE '$like' OR
              m.Author LIKE '$like' OR
              m.ISBN LIKE '$like' OR
              m.Publisher LIKE '$like' OR
              m.Genre LIKE '$like'
          )
        ORDER BY m.TypeID ASC, m.MaterialID DESC
    ");

    $books = []; $ebooks = []; $journals = [];

    while ($row = $result->fetch_assoc()) {
        if ($row['TypeID'] == 1) $books[] = $row;
        if ($row['TypeID'] == 2) $ebooks[] = $row;
        if ($row['TypeID'] == 3) $journals[] = $row;
    }

    return ['books' => $books, 'ebooks' => $ebooks, 'journals' => $journals];
}
?>