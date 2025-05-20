<?php
// backend/register.php
require_once 'config.php';

$full_name = $email = $password = $confirm_password = "";
$full_name_err = $email_err = $password_err = $confirm_password_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Validation du nom complet
    if(empty(trim($_POST["name"]))){
        $full_name_err = "Veuillez entrer votre nom complet.";
    } else {
        $full_name = trim($_POST["name"]);
    }

    // Validation de l'email
    if(empty(trim($_POST["email"]))){
        $email_err = "Veuillez entrer un email.";
    } else {
        // Vérifier si l'email existe déjà
        $sql = "SELECT id FROM users WHERE email = ?";

        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            $param_email = trim($_POST["email"]);

            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);

                if(mysqli_stmt_num_rows($stmt) == 1){
                    $email_err = "Cet email est déjà pris.";
                } else {
                    $email = trim($_POST["email"]);
                }
            } else {
                echo "Oops! Une erreur est survenue. Veuillez réessayer plus tard.";
            }
            mysqli_stmt_close($stmt);
        }
    }

    // Validation du mot de passe
    if(empty(trim($_POST["password"]))){
        $password_err = "Veuillez entrer un mot de passe.";
    } elseif(strlen(trim($_POST["password"])) < 6){
        $password_err = "Le mot de passe doit contenir au moins 6 caractères.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validation de la confirmation du mot de passe
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Veuillez confirmer le mot de passe.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "Les mots de passe ne correspondent pas.";
        }
    }

    // Vérifier les erreurs avant d'insérer dans la base de données
    if(empty($full_name_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err)){

        $sql = "INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)";

        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "sss", $param_full_name, $param_email, $param_password);

            $param_full_name = $full_name;
            $param_email = $email;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Hachage du mot de passe

            if(mysqli_stmt_execute($stmt)){
                // Redirection vers la page de connexion après inscription réussie
                header("location: ../pages/login.html?registered=true");
                exit();
            } else {
                echo "Quelque chose s'est mal passé. Veuillez réessayer plus tard.";
            }
            mysqli_stmt_close($stmt);
        }
    }

    // Si des erreurs existent, on pourrait rediriger l'utilisateur vers la page d'inscription avec les messages d'erreur.
    // Pour l'instant, nous allons simplement imprimer les erreurs (ce n'est pas idéal pour la production).
    // Une meilleure approche serait de stocker les erreurs dans des variables de session et de les afficher sur la page HTML.
    echo "<script>alert('Erreur d\'inscription :\\n" .
         (!empty($full_name_err) ? "- " . $full_name_err . "\\n" : "") .
         (!empty($email_err) ? "- " . $email_err . "\\n" : "") .
         (!empty($password_err) ? "- " . $password_err . "\\n" : "") .
         (!empty($confirm_password_err) ? "- " . $confirm_password_err . "\\n" : "") .
         "'); window.history.back();</script>";
}

mysqli_close($link);
?>