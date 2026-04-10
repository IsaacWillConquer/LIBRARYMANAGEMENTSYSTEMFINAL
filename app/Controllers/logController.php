<?php
ob_start();


require_once __DIR__ . "/../../config/dbConn.php";
require_once __DIR__ . "/../../core/auth.php";

header('Content-Type: application/json');

    // Only admin and analyst can access logss
    if (!isset($_SESSION['Role']) || !in_array($_SESSION['Role'], ['Admin', 'DataAnalyst'])) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
        exit();
    }

    $action = $_GET['action'] ?? '';

    switch ($action) {

        case 'getBorrowLogs':    
            getBorrowLogs();   
            break;
        case 'getLoginLogs':     
            getLoginLogs();     
            break;

        case 'getStaffLogs':     
            getStaffLogs();
            break;
        case 'getMemberLogs':    
            getMemberLogs();    
            break;
        case 'getMaterialLogs':  
            getMaterialLogs();  
            break;
        case 'getArchives':      
            getArchives();      
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Unknown action.']);
            break;
    }

    function getBorrowLogs() {
        $conn = getConnection('analyst');
        $result = $conn->query("CALL GetBorrowLogs()");
        $rows = [];
        
        while ($row = $result->fetch_assoc()) $rows[] = $row;
        echo json_encode(['success' => true, 'data' => $rows]);
    }

    function getLoginLogs() {
        $conn = getConnection('analyst');
        $result = $conn->query("CALL GetLoginLogs()");
        $rows = [];
        while ($row = $result->fetch_assoc()) $rows[] = $row;
        echo json_encode(['success' => true, 'data' => $rows]);
    }

    function getStaffLogs() {
        $conn = getConnection('analyst');
        $result = $conn->query("CALL GetStaffLogs()");
        $rows = [];
        while ($row = $result->fetch_assoc()) $rows[] = $row;
        echo json_encode(['success' => true, 'data' => $rows]);
    }

    function getMemberLogs() {
        $conn = getConnection('analyst');
        $result = $conn->query("CALL GetMemberLogs()");
        $rows = [];
        while ($row = $result->fetch_assoc()) $rows[] = $row;
        echo json_encode(['success' => true, 'data' => $rows]);
    }

    function getMaterialLogs() {
        $conn = getConnection('analyst');
        $result = $conn->query("CALL GetMaterialLogs()");
        $rows = [];
        while ($row = $result->fetch_assoc()) $rows[] = $row;
        echo json_encode(['success' => true, 'data' => $rows]);
    }

    function getArchives() {
        $conn = getConnection('analyst');
        $type = $_GET['type'] ?? '';
        $validTypes = ['Staff', 'Member', ''];
        
        if (!in_array($type, $validTypes)) {
            echo json_encode(['success' => false, 'message' => 'Invalid type.']);
            exit();
        }


        $stmt = $conn->prepare("CALL GetArchives(?)");
        $stmt->bind_param("s", $type);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = [];
        while ($row = $result->fetch_assoc()) $rows[] = $row;
        echo json_encode(['success' => true, 'data' => $rows]);
    }
    ?>