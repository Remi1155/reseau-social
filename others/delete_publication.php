<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id_compte'])) {
    header('Location: ../index.php');
    exit();
}

// Vérifier si l'ID de la publication est envoyé
if (isset($_POST['id_publication'])) {
    // Connexion à la base de données
    require_once "../config/config.php";

    // Vérifier si la publication appartient à l'utilisateur connecté
    $stmt = $pdo->prepare("SELECT id_compte FROM publication WHERE id_publication = ?");
    $stmt->execute([$_POST['id_publication']]);
    $publication = $stmt->fetch();

    if ($publication && $publication['id_compte'] == $_SESSION['id_compte']) {
        // L'utilisateur est bien l'auteur, suppression de la publication
        $stmt = $pdo->prepare("DELETE FROM publication WHERE id_publication = ?");
        $stmt->execute([$_POST['id_publication']]);

        // Rediriger vers la page d'accueil après suppression
        header('Location: ./home.php');
        exit();
    } else {
        // L'utilisateur n'est pas autorisé à supprimer cette publication
        echo "Vous n'êtes pas autorisé à supprimer cette publication.";
    }
} else {
    // Si aucun id de publication n'a été envoyé
    echo "Aucune publication spécifiée.";
}