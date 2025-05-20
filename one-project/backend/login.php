<?php
// backend/login.php
session_start(); // Démarre la session PHP
require_once 'config.php';

$email = $password = "";
$email_err = $password_err = $login_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Validation de l'email
    if(empty(trim($_POST["email"]))){
        $email_err = "Veuillez entrer votre email.";
    } else {
        $email = trim($_POST["email"]);
    }

    // Validation du mot de passe
    if(empty(trim($_POST["password"]))){
        $password_err = "Veuillez entrer votre mot de passe.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Vérifier les identifiants
    if(empty($email_err) && empty($password_err)){
        $sql = "SELECT id, full_name, email, password FROM users WHERE email = ?";

        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            $param_email = $email;

            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);

                // Vérifier si l'email existe, puis vérifier le mot de passe
                if(mysqli_stmt_num_rows($stmt) == 1){
                    mysqli_stmt_bind_result($stmt, $id, $full_name, $email, $hashed_password);
                    if(mysqli_stmt_fetch($stmt)){
                        if(password_verify($password, $hashed_password)){
                            // Mot de passe correct, démarrer une nouvelle session
                            session_start();

                            // Stocker les données dans les variables de session
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["email"] = $email;
                            $_SESSION["full_name"] = $full_name; // Stocke le nom complet

                            // Redirection vers la page du tableau de bord
                            header("location: ../pages/dashboard.php"); // Noter .php ici
                            exit();
                        } else {
                            // Mot de passe invalide
                            $login_err = "Email ou mot de passe invalide.";
                        }
                    }
                } else {
                    // Email non trouvé
                    $login_err = "Email ou mot de passe invalide.";
                }
            } else {
                echo "Oops! Une erreur est survenue. Veuillez réessayer plus tard.";
            }
            mysqli_stmt_close($stmt);
        }
    }

    // Si des erreurs de connexion, rediriger avec un message d'alerte.
    if (!empty($login_err)) {
        echo "<script>alert('Erreur de connexion : " . $login_err . "'); window.history.back();</script>";
    }
}

mysqli_close($link);
?>