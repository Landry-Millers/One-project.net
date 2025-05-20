<?php
// backend/add_task.php
session_start();
require_once 'config.php';

// Vérifier si l'utilisateur est connecté
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: ../pages/login.html");
    exit;
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $project_id = trim($_POST["project_id"]);
    $task_name = trim($_POST["task_name"]);
    $description = trim($_POST["description"]);
    $due_date = trim($_POST["due_date"]);
    $assigned_to = trim($_POST["assigned_to"]);
    $status = trim($_POST["status"]);

    // Simple validation
    if(empty($task_name) || empty($project_id)){
        echo "<script>alert('Le nom de la tâche et le projet sont requis.'); window.history.back();</script>";
        exit;
    }

    $sql = "INSERT INTO tasks (project_id, task_name, description, due_date, assigned_to, status) VALUES (?, ?, ?, ?, ?, ?)";

    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "isssss", $param_project_id, $param_task_name, $param_description, $param_due_date, $param_assigned_to, $param_status);

        $param_project_id = $project_id;
        $param_task_name = $task_name;
        $param_description = $description;
        $param_due_date = !empty($due_date) ? $due_date : NULL;
        $param_assigned_to = !empty($assigned_to) ? $assigned_to : NULL;
        $param_status = $status;

        if(mysqli_stmt_execute($stmt)){
            header("location: ../pages/dashboard.php?task_added=true");
            exit();
        } else {
            echo "<script>alert('Erreur lors de l\'ajout de la tâche : " . mysqli_error($link) . "'); window.history.back();</script>";
        }
        mysqli_stmt_close($stmt);
    }
}

mysqli_close($link);
?>