<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: dashboarduser.php');
    exit();
}

$project_id = $_GET['id'];
$user = $_SESSION['user'];

try {
    // First check if the project belongs to the user
    $check_stmt = $conn->prepare("SELECT id_user FROM Projects WHERE project_id = :project_id");
    $check_stmt->bindParam(':project_id', $project_id);
    $check_stmt->execute();
    $project = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$project || $project['id_user'] != $user['id_user']) {
        header('Location: dashboarduser.php');
        exit();
    }
    
    // Delete the project
    $stmt = $conn->prepare("DELETE FROM Projects WHERE project_id = :project_id AND id_user = :id_user");
    $stmt->bindParam(':project_id', $project_id);
    $stmt->bindParam(':id_user', $user['id_user']);
    $stmt->execute();
    
    header('Location: dashboarduser.php');
    exit();
} catch(PDOException $e) {
    $_SESSION['error'] = "Error deleting project: " . $e->getMessage();
    header('Location: dashboarduser.php');
    exit();
}
?>
