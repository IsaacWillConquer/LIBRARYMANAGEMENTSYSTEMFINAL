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
        markUnclaimed();
        echo json_encode(getPendingReq());
        break;

    case 'approveReq':
        $requestID = intval($_POST['requestID'] ?? 0);
        $claimDeadline = trim($_POST['claimDeadline'] ?? '');
        if (!$requestID || !$claimDeadline) {
            echo json_encode(['success' => false, 'message' => 'Missing request ID or claim-deadline']);
            break;
        }

        echo json_encode(approveReq($requestID, $staffID, $claimDeadline));
        break;

    case 'rejectReq':
        $requestID = intval($_POST['requestID'] ?? 0);
        $remarks = trim($_POST['remarks'] ?? '');

        if (!$requestID) {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            break;
        }

        echo json_encode(rejectReq($requestID, $staffID, $remarks));
        break;

    case 'getActiveBorrows':
        markOverdue();
        markUnclaimed();
        echo json_encode(getActiveBorrow());
        break;

    case 'getApprovedClaims':
        markUnclaimed();
        echo json_encode(getApprovedClaims());
        break;

    case 'markClaimed':
        $requestID = intval($_POST['requestID'] ?? 0);
        if (!$requestID) {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            break;
        }
        echo json_encode(markClaimed($requestID, $staffID));
        break;

    case 'returnBook':
        $recordID = intval($_POST['recordID'] ?? 0);
        if (!$recordID) {
            echo json_encode(['success' => false, 'message' => 'Invalid record']);
            break;
        }
        echo json_encode(returnBook($recordID, $staffID));
        break;

    case 'getPendingDonations':
        echo json_encode(getPendingDonations());
        break;

    case 'reviewDonation':
        $donationID = intval($_POST['donationID'] ?? 0);
        $status = trim($_POST['status'] ?? '');
        if (!$donationID || !in_array($status, ['Accepted', 'Rejected'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid donation review']);
            break;
        }
        echo json_encode(reviewDonation($donationID, $staffID, $status));
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}