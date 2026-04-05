<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

ob_start();

session_start();

include __DIR__ . "/../../core/auth.php";
include __DIR__ . "/../Models/memberOperations.php";

header('Content-Type: application/json');

mustBeStaff();

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$adminID = $_SESSION['StaffID'];

ob_end_clean();
switch ($action) {

    case 'getAll':
        echo json_encode(getMembers());
        break;

    case 'getStatuses':
        echo json_encode(getMemberStatuses());
        break;

    case 'getOne':
        $id = intval($_GET['id']);
        echo json_encode(getMemberByID($id));
        break;

    case 'addMember':
        $fname = trim($_POST['fname'] ?? '');
        $lname = trim($_POST['lname'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $statusID = intval($_POST['statusID'] ?? 0);

        if (!$fname || !$lname || !$email || !$statusID) {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            break;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email format']);
            break;
        }
        echo json_encode(addMember($fname, $lname, $email, $statusID, $adminID));
        break;

    case 'updMember':
        $memberID = intval($_POST['memberID'] ?? 0);
        $fname = trim($_POST['fname'] ?? '');
        $lname = trim($_POST['lname'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $statusID = intval($_POST['statusID'] ?? 0);

        if (!$fname || !$lname || !$email || !$statusID) {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            break;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email format']);
            break;
        }
        echo json_encode(updMember($memberID, $fname, $lname, $email, $statusID, $adminID));
        break;

    case 'archiveMember':
        $memberID = intval($_POST['memberID'] ?? 0);
        if (!$memberID) {
            echo json_encode(['success' => false, 'message' => 'Invalid member ID']);
            break;
        }
        echo json_encode(archiveMember($memberID, $adminID));
        break;

    case 'search':
        $q = trim($_GET['q'] ?? '');
        if ($q === '') {
            echo json_encode(getMembers());
        } else {
            echo json_encode(searchMembers($q));
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>