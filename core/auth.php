<?php


session_start();

    function mustBeStaff ($role = null) {
        if (!isset($_SESSION['StaffID'])) {
            header('Location: /index.php');
            exit();
        }
        if ($role && ($_SESSION['Role'] ?? '') !== $role) {
            header('Location: /index.php');
            exit();
        }
    }

    function mustBeMember () {
        if (!isset($_SESSION['MemberID'])) {
            header('Location: /index.php');
            exit();
        }
    }


    function checkStaffAccess($role = null) {
        if (!isset($_SESSION['StaffID'])) {
            echo json_encode(['success' => false, 'message' => 'You shall not pass..']);
            exit();
        }
        if ($role && ($_SESSION['Role'] ?? '') !== $role) {
            echo json_encode(['success' => false, 'message' => 'You shall not pass.']);
            exit();
        }
    }

    function checkMemberAccess() {
        if (!isset($_SESSION['MemberID'])) {
            echo json_encode(['success' => false, 'message' => 'You shall not pass..']);
            exit();
        }
    }

    //if not change pass for 1st time
    function isDefaultPassword(): bool {
        return isset($_SESSION['DefaultPassword']) && $_SESSION['DefaultPassword'] == 1;
    }