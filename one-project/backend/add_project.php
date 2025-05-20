<?php
// backend/add_project.php
session_start();
require_once 'config.php';

// Vérifier si l'utilisateur est connecté
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: ../pages/login.html");
    exit;
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $user_id = $_SESSION["id"];
    $project_name = trim($_POST["project_name"]);
    $description = trim($_POST["description"]);
    $start_date = trim($_POST["start_date"]);
    $end_date = trim($_POST["end_date"]);
    $status = trim($_POST["status"]);

    // Simple validation (peut être étendue)
    if(empty($project_name)){
        echo "<script>alert('Le nom du projet est requis.'); window.history.back();</script>";
        exit;
    }

    $sql = "INSERT INTO projects (user_id, project_name, description, start_date, end_date, status) VALUES (?, ?, ?, ?, ?, ?)";

    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "isssss", $param_user_id, $param_project_name, $param_description, $param_start_date, $param_end_date, $param_status);

        $param_user_id = $user_id;
        $param_project_name = $project_name;
        $param_description = $description;
        $param_start_date = !empty($start_date) ? $start_date : NULL;
        $param_end_date = !empty($end_date) ? $end_date : NULL;
        $param_status = $status;

        if(mysqli_stmt_execute($stmt)){
            header("location: ../pages/dashboard.php?project_added=true");
            exit();
        } else {
            echo "<script>alert('Erreur lors de l\'ajout du projet : " . mysqli_error($link) . "'); window.history.back();</script>";
        }
        mysqli_stmt_close($stmt);
    }
}

mysqli_close($link);
?>