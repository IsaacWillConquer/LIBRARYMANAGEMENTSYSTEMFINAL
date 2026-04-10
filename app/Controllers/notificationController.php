<?php
ob_start();
session_start();

require_once __DIR__ . "/../../core/auth.php";
require_once __DIR__ . "/../../config/dbConn.php";
include_once __DIR__ . "/../Models/notificationOperations.php";

$conn = getConnection();

header('Content-Type: application/json');


        $action = $_POST['action'] ?? $_GET['action'] ?? '';


        if (isset($_SESSION['MemberID'])) {
            $userID = $_SESSION['MemberID'];
            $userType = 'Member';
        } else if (isset($_SESSION['StaffID'])) {
            $userID = $_SESSION['StaffID'];
            $userType = 'Staff';
        } else {
            ob_end_clean();
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }


        switch ($action) {
            case 'getNotifications':
                ob_end_clean();
                echo json_encode(getNotifications($userID, $userType));
                break;

            case 'getUnreadCount':
                ob_end_clean();
                echo json_encode(['count' => getUnreadCount($userID, $userType)]);
                break;

            case 'markAllRead':
                markAllRead($userID, $userType);
                ob_end_clean();
                echo json_encode(['success' => true]);
                break;

            default:
                ob_end_clean();
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
                break;
        }