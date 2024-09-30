<?php
session_start();
if (!isset($_SESSION['id_compte'])) {
    header('Location: ../login.php');
    exit();
}

// Connexion à la base de données
require_once "../config/config.php";

// Ajout d'un commentaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_publication = $_POST['id_publication'];
    $contenu = trim($_POST['commentaire']);
    if ($contenu !== '') {
        $stmt = $pdo->prepare("INSERT INTO comments (id_publication, id_compte, contenu) VALUES (?, ?, ?)");
        $stmt->execute([$id_publication, $_SESSION['id_compte'], $contenu]);
    } else {
        echo 'Le commentaire est vide';
    }

    // Redirection vers la page affichant tous les commentaires
    header("Location: ./show_comments.php?id_publication=" . $id_publication);

    exit();
}
