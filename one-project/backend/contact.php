<?php
// backend/contact.php
require_once 'config.php';

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $subject = trim($_POST["subject"]); // Supposant que vous avez un champ 'subject'
    $message = trim($_POST["message"]);

    // Simple validation
    if(empty($name) || empty($email) || empty($message)){
        echo "<script>alert('Veuillez remplir tous les champs obligatoires (Nom, Email, Message).'); window.history.back();</script>";
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Le format de l\'email est invalide.'); window.history.back();</script>";
        exit;
    }

    $sql = "INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)";

    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "ssss", $param_name, $param_email, $param_subject, $param_message);

        $param_name = $name;
        $param_email = $email;
        $param_subject = $subject;
        $param_message = $message;

        if(mysqli_stmt_execute($stmt)){
            echo "<script>alert('Votre message a été envoyé avec succès !'); window.history.back();</script>";
            // Ou redirection vers une page de confirmation
            // header("location: ../pages/contact.html?success=true");
            exit();
        } else {
            echo "<script>alert('Une erreur est survenue lors de l\'envoi du message : " . mysqli_error($link) . "'); window.history.back();</script>";
        }
        mysqli_stmt_close($stmt);
    }
}

mysqli_close($link);
?>