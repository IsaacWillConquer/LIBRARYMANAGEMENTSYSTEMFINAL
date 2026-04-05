<?php

function createNotification($userID, $userType, $message) {
    global $conn;

    $stmt = $conn->prepare("
        INSERT INTO notifications (UserID, UserType, Message)
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param('iss', $userID, $userType, $message);
    $stmt->execute();
}

function getNotifications($userID, $userType) {
    global $conn;

    $stmt = $conn->prepare("
        SELECT * FROM notifications
        WHERE UserID = ? AND UserType = ?
        ORDER BY DateCreated DESC
        LIMIT 20
    ");
    $stmt->bind_param('is', $userID, $userType);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) $data[] = $row;
    return $data;
}

function markAllRead($userID, $userType) {
    global $conn;

    $stmt = $conn->prepare("
        UPDATE notifications SET IsRead = 1
        WHERE UserID = ? AND UserType = ?
    ");
    $stmt->bind_param('is', $userID, $userType);
    $stmt->execute();
}

function getUnreadCount($userID, $userType) {
    global $conn;

    $stmt = $conn->prepare("
        SELECT COUNT(*) AS cnt FROM notifications
        WHERE UserID = ? AND UserType = ? AND IsRead = 0
    ");
    $stmt->bind_param('is', $userID, $userType);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row['cnt'];
}