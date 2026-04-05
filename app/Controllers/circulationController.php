<?php
ob_start();

session_start();

include __DIR__ . "/../../core/auth.php";
include __DIR__ . "/../Models/circulationOperations.php";

header('Content-Type: application/json');

mustBeStaff();

$staffID = $_SESSION['StaffID'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

ob_end_clean();
switch ($action) {

    case 'getPendingReq':
        markOverdue();
        echo json_encode(getPendingReq());
        break;

    case 'approveReq':
        $requestID = intval($_POST['requestID'] ?? 0);
        if (!$requestID) {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            break;
        }
        echo json_encode(approveReq($requestID, $staffID));
        break;

    case 'rejectReq':
        $requestID = intval($_POST['requestID'] ?? 0);
        if (!$requestID) {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            break;
        }
        echo json_encode(rejectReq($requestID, $staffID));
        break;

    case 'getActiveBorrows':
        markOverdue();
        echo json_encode(getActiveBorrow());
        break;

    case 'returnBook':
        $recordID = intval($_POST['recordID'] ?? 0);
        if (!$recordID) {
            echo json_encode(['success' => false, 'message' => 'Invalid record']);
            break;
        }
        echo json_encode(returnBook($recordID, $staffID));
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}