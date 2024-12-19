<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$user = $_SESSION['user'];

// Edit project
if (isset($_POST['project_id'])) {
    $project_id = $_POST['project_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];
    
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

        $stmt = $conn->prepare("UPDATE Projects SET title = :title, description = :description, category_id = :category_id WHERE project_id = :id AND id_user = :id_user");
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':id', $project_id);
        $stmt->bindParam(':id_user', $user['id_user']);
        $stmt->execute();
        
        header('Location: dashboarduser.php');
        exit();
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error updating project: " . $e->getMessage();
        header('Location: dashboarduser.php');
        exit();
    }
}
