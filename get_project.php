<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Project ID not provided']);
    exit();
}

$project_id = $_GET['id'];
$user = $_SESSION['user'];

try {
    $stmt = $conn->prepare("SELECT * FROM Projects WHERE project_id = :project_id AND id_user = :id_user");
    $stmt->bindParam(':project_id', $project_id);
    $stmt->bindParam(':id_user', $user['id_user']);
    $stmt->execute();
    
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($project) {
        header('Content-Type: application/json');
        echo json_encode($project);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Project not found']);
    }
} catch(PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
?>
