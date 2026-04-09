<?php
ob_start();

session_start();

include __DIR__ . "/../../core/auth.php";
include __DIR__ . "/../Models/materialOperations.php";

header('Content-Type: application/json');

checkStaffAccess();

$role = $_SESSION['Role'];
$staffID = $_SESSION['StaffID'];


if ($role !== 'Admin' && $role !== 'CirculationLibrarian') {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

ob_end_clean();
switch ($action) {

    case 'getAll':
        echo json_encode([
            'books' => getBooks(),
            'ebooks' => getEbooks(),
            'journals' => getJournals()
        ]);
        break;

    case 'getOne':
        $id = intval($_GET['id'] ?? 0);
        echo json_encode(getMaterialByID($id));
        break;

    case 'addBook':
        $data = collectBase();
        if ($data['error']) { 
            echo json_encode(['success' => false, 'message' => $data['error']]);
             break; 
        }

        echo json_encode(addBook($data, $staffID));
        break;

    case 'addEbook':
        $data = collectBase();
        if ($data['error']) { 
            echo json_encode(['success' => false, 'message' => $data['error']]); 
            break; 
        }

        $data['ebookFormat'] = trim($_POST['ebookFormat'] ?? '');
        $data['accessStart'] = trim($_POST['accessStart'] ?? '');
        $data['accessEnd'] = trim($_POST['accessEnd'] ?? '');

        if (!$data['ebookFormat']) {
            echo json_encode(['success' => false, 'message' => 'EBook format is required']);
            break;
        }
        echo json_encode(addEbook($data, $staffID));
        break;

    case 'addJournal':
        $data = collectBase();
        if ($data['error']) { 
            echo json_encode(['success' => false, 'message' => $data['error']]);
             break; 
        }

        $data['journalType'] = trim($_POST['journalType'] ?? '');
        $data['journalInterval'] = trim($_POST['journalInterval'] ?? '');

        if (!$data['journalType']) {
            echo json_encode(['success' => false, 'message' => 'Journal type is required']); 
            break;
        }

        if (!$data['journalInterval']) {
            echo json_encode(['success' => false, 'message' => 'Journal interval is required']);
            break;
        }

        echo json_encode(addJournal($data, $staffID));
        break;

    case 'updMaterial':
        $data = collectBase();
        $data['materialID'] = intval($_POST['materialID'] ?? 0);
        $data['availableQty'] = intval($_POST['availableQty'] ?? 0);
        $data['ebookFormat'] = trim($_POST['ebookFormat'] ?? '');
        $data['accessStart'] = trim($_POST['accessStart'] ?? '');
        $data['accessEnd'] = trim($_POST['accessEnd'] ?? '');
        $data['journalType'] = trim($_POST['journalType'] ?? '');
        $data['journalInterval'] = trim($_POST['journalInterval'] ?? '');

        if (!$data['materialID']) { 
            echo json_encode(['success' => false, 'message' => 'Invalid material ID']); 
            break; 
        }
        
        if ($data['error']) { 
            echo json_encode(['success' => false, 'message' => $data['error']]);
            break;
        }

        echo json_encode(updMaterial($data, $staffID));
        break;

    case 'archiveMaterial':
        $materialID = intval($_POST['materialID'] ?? 0);

        if (!$materialID) {
            echo json_encode(['success' => false, 'message' => 'Invalid material ID']);
            break; 
        }

        echo json_encode(archiveMaterial($materialID, $staffID));
        break;

    case 'search':
        $q = trim($_GET['q'] ?? '');
        if ($q === '') {
            echo json_encode(['books' => getBooks(), 'ebooks' => getEbooks(), 'journals' => getJournals()]);
        } else {
            echo json_encode(searchMaterials($q));
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function collectBase() {
    $data = [
        'title' => trim($_POST['title'] ?? ''),
        'author' => trim($_POST['author'] ?? ''),
        'isbn' => trim($_POST['isbn'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'genre' => trim($_POST['genre'] ?? ''),
        'publishDate' => trim($_POST['publishDate'] ?? ''),
        'publisher' => trim($_POST['publisher'] ?? ''),
        'totalQty' => intval($_POST['totalQty'] ?? 1),
        'replacementCost' => floatval($_POST['replacementCost'] ?? 0),
        'error' => null,
    ];

    if (!$data['title']){
        $data['error'] = 'Title is required';
    } else if (!$data['author']) { 
        $data['error'] = 'Author is required';
    } else if (!$data['publisher']) {
        $data['error'] = 'Publisher is required';
    } 

    return $data;
}
?>