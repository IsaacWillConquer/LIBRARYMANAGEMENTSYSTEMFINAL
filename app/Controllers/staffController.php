<?php
ob_start();

session_start();

include __DIR__ . "/../Models/staffOperations.php";

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$adminID = $_SESSION['StaffID'];

//ob_end_clean() clears accidental output before json_encode
ob_end_clean();
    switch ($action) {

        case 'getAll':
            echo json_encode(getStaff($adminID));
            break;

        case 'getRoles':
            echo json_encode(getRoles());
            break;

        case 'getStatuses':
            echo json_encode(getStatuses());
            break;

        case 'getOne':
            $id = intval($_GET['id']);
            echo json_encode(getStaffByID($id));
            break;

        case 'addStaff':
            $fname = trim($_POST['fname'] ?? '');
            $lname = trim($_POST['lname'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $roleID = intval($_POST['roleID'] ?? 0);
            $statusID = intval($_POST['statusID'] ?? 0);

            if (!$fname || !$lname || !$email || !$roleID || !$statusID) {
                echo json_encode(['success' => false, 'message' => 'All fields are required']);
                break;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'message' => 'Invalid email format']);
                break;
            }

            echo json_encode(addStaff($fname, $lname, $email, $roleID, $statusID, $adminID));
            break;

        case 'updStaff':
            $staffID = intval($_POST['staffID'] ?? 0);
            $fname = trim($_POST['fname'] ?? '');
            $lname = trim($_POST['lname'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $roleID = intval($_POST['roleID'] ?? 0);
            $statusID = intval($_POST['statusID'] ?? 0);

            if (!$staffID || !$fname || !$lname || !$email || !$roleID || !$statusID) {
                echo json_encode(['success' => false, 'message' => 'All fields are required']);
                break;
            }

            //check input new email if valid in upd
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'message' => 'Invalid email format']);
                break;
            }

            echo json_encode(updStaff($staffID, $fname, $lname, $email, $roleID, $statusID, $adminID));
            break;

        case 'search':
            $q = trim($_GET['q'] ?? '');
            if ($q ==='') {
                echo json_encode(getStaff($adminID));
            } else {
                echo json_encode(searchStaff($q));
            }
            break;

        case 'archiveStaff':
            $staffID = intval($_POST['staffID'] ?? 0);

            if (!$staffID) {
                echo json_encode(['success' => false, 'message' => 'Invalid staff ID']);
                break;
            }

            echo json_encode(archiveStaff($staffID, $adminID));
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
    ?>