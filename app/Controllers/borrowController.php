<?php
//tldr member-side borrow actions

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

        case 'accessEbook':
            $materialID = intval($_POST['materialID'] ?? 0);

            if (!$materialID) {
                echo json_encode(['success' => false, 'message' => 'Invalid material']);
                break;
            }

            echo json_encode(accessEbook($memberID, $materialID));
            break;

        case 'getCurrentlyReading':
            echo json_encode(getCurrentlyReading($memberID));
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
            $type = trim($_GET['type'] ?? '');
            echo json_encode(searchAvailMats($q, $type));
            break;

        case 'getPendingClaims':
            echo json_encode(getMemberPendingClaims($memberID));
            break;

        case 'cancelClaim':
            $requestID = intval($_POST['requestID'] ?? 0);
            if (!$requestID) {
                echo json_encode(['success' => false, 'message' => 'Invalid request']);
                break;
            }
            echo json_encode(cancelPendingClaim($requestID, $memberID));
            break;

        case 'donate':
            $title = trim($_POST['title'] ?? '');
            $author = trim($_POST['author'] ?? '');
            $genre = trim($_POST['genre'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $condition = trim($_POST['condition'] ?? 'Good');
            echo json_encode(submitDonation($memberID, $title, $author, $genre, $description, $condition));
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }