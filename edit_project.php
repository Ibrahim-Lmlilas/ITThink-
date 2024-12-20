<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $project_id = $_POST['project_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];
    $user_id = $_SESSION['user']['id_user'];

    try {
        // First verify that this project belongs to the current user
        $check_stmt = $conn->prepare("SELECT id_user FROM Projects WHERE project_id = :project_id");
        $check_stmt->bindParam(':project_id', $project_id);
        $check_stmt->execute();
        $project = $check_stmt->fetch();

        if ($project && $project['id_user'] == $user_id) {
            // Update the project
            $stmt = $conn->prepare("UPDATE Projects SET title = :title, description = :description, category_id = :category_id WHERE project_id = :project_id AND id_user = :user_id");
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':category_id', $category_id);
            $stmt->bindParam(':project_id', $project_id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();

            $_SESSION['success'] = "Project updated successfully!";
        } else {
            $_SESSION['error'] = "You don't have permission to edit this project.";
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error updating project: " . $e->getMessage();
    }
}

header("Location: dashboarduser.php");
exit();
?>
