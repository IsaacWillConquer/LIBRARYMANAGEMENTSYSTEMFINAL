<?php
require_once __DIR__ . '/databaseInfo.php';

function getConnection($role = null) {
    static $connections = [];


    if ($role === null) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $role = $_SESSION['DBRole'] ?? 'member';
    }

    
    if (!isset($connections[$role])) {
        switch ($role) {
            case 'admin':
                $creds = DB_ADMIN;
                break;
            case 'circulation':
                $creds = DB_CIRCULATION;
                break;
            case 'analyst':
                $creds = DB_ANALYST;
                break;
            default:
                $creds = DB_MEMBER;
                break;
        }

        $conn = new mysqli(DB_HOST, $creds['user'], $creds['pass'], DB_NAME);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $connections[$role] = $conn;
    }

    return $connections[$role];
}
?>