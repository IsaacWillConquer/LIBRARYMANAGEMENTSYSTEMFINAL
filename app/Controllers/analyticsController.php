<?php
ob_start();

session_start();

include __DIR__ . "/../../core/auth.php";
include __DIR__ . "/../Models/analyticsOperations.php";

header('Content-Type: application/json');

mustBeStaff();

$role = $_SESSION['Role'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($role !== 'Admin' && $role !== 'DataAnalyst') {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Access denied, You cant be here!']);
    exit();
}

ob_end_clean();
switch ($action) {

    case 'getSummary':
        echo json_encode(summaryStats());
        break;

    case 'getBorrowsMonthly':
        echo json_encode(borrowMontly());
        break;

    case 'getTopBorrow':
        echo json_encode(topBorrow());
        break;

    case 'getborrowStats':
        echo json_encode(borrowStats());
        break;

    case 'getMatType':
        echo json_encode(matType());
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action bro.']);
        break;
}