<?php
//tldr member-side borrow actions
//checkMemberAccess() for permission check
//intval = only whole numbers, no funny business

ob_start();

session_start();

include __DIR__ . "/../../core/auth.php";
include __DIR__ . "/../Models/borrowOperations.php";

header('Content-Type: application/json');

checkMemberAccess();

$memberID = $_SESSION['MemberID'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

ob_end_clean();
switch ($action) {

    case 'getCats':
        echo json_encode([
            'trending' => getTrend(),
            'recommended' => getRecs($memberID),
            'all' => getAvailMats()
        ]);
        break;

    case 'getMyReq':
        echo json_encode(getMyReq($memberID));
        break;

    case 'getMyBorrows':
        echo json_encode(getMyActBorrows($memberID));
        break;

    case 'submitReq':
        $materialID = intval($_POST['materialID'] ?? 0);
        if (!$materialID) {
            echo json_encode(['success' => false, 'message' => 'Invalid material']);
            break;
        }
        echo json_encode(submitReq($memberID, $materialID));
        break;

    case 'cancelReq':
        $requestID = intval($_POST['requestID'] ?? 0);
        if (!$requestID) {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            break;
        }
        echo json_encode(cancelReq($memberID, $requestID));
        break;

    case 'getHistory':
        echo json_encode(getBorrowHist($memberID));
        break;
        
    case 'search':
        $q = trim($_GET['q'] ?? '');
        echo json_encode(searchAvailMats($q));
    break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}