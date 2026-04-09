<?php
//VALIDATIONS for logins, auth
//tldr login or change pass

//in changepass if defaultPass = 1 means that mf has not set their own pass yet
//means that account is new

ob_start();

session_start();

include __DIR__ . "/../Models/authOperations.php";
require_once __DIR__ . "/../../config/dbConn.php";

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';


switch ($action) {
    case 'loginDaUser':
        login();
        break;
    case 'changepass':
        changepass();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
        break;
}

function login() {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';

    if (!$email || !$password) {
        echo json_encode(['success' => false, 'message' => 'Email and password are required.']);
        exit();
    }

    $staff = staffEmail($email);
    if ($staff) {
        if ($staff['Status'] === 'Resigned' || $staff['Status'] === 'Suspended') {
            logLogin('Staff', $staff['StaffID'], $email, 'Failed', $ip);
            echo json_encode(['success' => false, 'message' => 'Your account is ' . $staff['Status'] . '. Please Contact admin.']);
            exit();
        }

        if (!password_verify($password, $staff['Password'])) {
            logLogin('Staff', null, $email, 'Failed', $ip);
            echo json_encode(['success' => false, 'message' => 'Incorrect password.']);
            exit();
        }

        $_SESSION['StaffID'] = $staff['StaffID'];
        $_SESSION['Role'] = $staff['Role'];
        $_SESSION['FullName'] = $staff['FirstName'] . ' ' . $staff['LastName'];
        $_SESSION['DefaultPassword'] = $staff['DefaultPassword'];

        logLogin('Staff', $staff['StaffID'], $email, 'Success', $ip);

        if ($staff['DefaultPassword'] == 1) {
            echo json_encode(['success' => true, 'redirect' => 'app/Views/Auth/changePass.php']);
            exit();
        }

        switch ($staff['Role']) {
            case 'Admin':
                $redirect = 'app/Views/Dashboards/adminDashboard.php';
                break;
            case 'CirculationLibrarian':
                $redirect = 'app/Views/Circulation/manageBorrows.php';
                break;
            case 'DataAnalyst':
                $redirect = 'app/Views/Dashboards/analystDashboard.php';
                break;
            default:
                $redirect = '/index.php';
                break;
        }
        echo json_encode(['success' => true, 'redirect' => $redirect]);
        exit();
    }

    //member check
    $member = memberEmail($email);
    if (!$member) {
        logLogin('Member', null, $email, 'Failed', $ip);
        echo json_encode(['success' => false, 'message' => 'No account found with that email.']);
        exit();
    }

    if ($member['Status'] === 'Suspended' || $member['Status'] === 'Inactive') {
        logLogin('Member', $member['MemberID'], $email, 'Failed', $ip);
        echo json_encode(['success' => false, 'message' => 'Your account is ' . $member['Status'] . '. Please Contact the librarian']);
        exit();
    }

    if (!password_verify($password, $member['Password'])) {
        logLogin('Member', null, $email, 'Failed', $ip);
        echo json_encode(['success' => false, 'message' => 'Incorrect password.']);
        exit();
    }

    $_SESSION['MemberID'] = $member['MemberID'];
    $_SESSION['Role'] = 'Member';
    $_SESSION['FullName'] = $member['FirstName'] . ' ' . $member['LastName'];
    $_SESSION['DefaultPassword'] = $member['DefaultPassword'];

    logLogin('Member', $member['MemberID'], $email, 'Success', $ip);

    //DefaultPassword flag - if 1 means they havent changed it yet
    if ($member['DefaultPassword'] == 1) {
        echo json_encode(['success' => true, 'redirect' => 'app/Views/Auth/changepass.php']);
        exit();
    }

    echo json_encode(['success' => true, 'redirect' => 'app/Views/Dashboards/memberDashboard.php']);
    exit();
}

function logout() {
    session_unset();
    session_destroy();
    echo json_encode(['success' => true, 'redirect' => '/index.php']);
    exit();
}

function changepass() {
    $newpass = $_POST['newpass'] ?? '';

    if (strlen($newpass) < 7) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 7 characters.']);
        exit();
    }

    //hash it before storing
    $hashed = password_hash($newpass, PASSWORD_DEFAULT);

    if (isset($_SESSION['StaffID'])) {
        $id = intval($_SESSION['StaffID']);
        updStaffPass($id, $hashed);
        $_SESSION['DefaultPassword'] = 0;

        switch ($_SESSION['Role']) {
            case 'Admin':
                $redirect = 'app/Views/Dashboards/adminDashboard.php';
                break;
            case 'CirculationLibrarian':
                $redirect = 'app/Views/Circulation/manageBorrow.php';
                break;
            case 'DataAnalyst':
                $redirect = 'app/Views/Dashboards/analystDashboard.php';
                break;
            default:
                $redirect = '/index.php';
                break;
        }

        echo json_encode(['success' => true, 'redirect' => $redirect]);
        exit();

    } else if (isset($_SESSION['MemberID'])) {
        $id = intval($_SESSION['MemberID']);
        updMemPass($id, $hashed);
        $_SESSION['DefaultPassword'] = 0;
        echo json_encode(['success' => true, 'redirect' => 'app/Views/Dashboards/memberDashboard.php']);
        exit();

    } else {
        echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
        exit();
    }
}
?>