<?php
session_start();
if (!isset($_SESSION['id_compte'])) {
    header('Location: ../others/home.php');
    exit();
}

// Connexion à la base de données
require_once '../config/config.php';

// Récupération de l'ID du commentaire, de la publication et du type de réaction depuis le formulaire
$id_comment = $_POST['id_comment'];
$id_publication = $_POST['id_publication'];
$type = $_POST['type'];
$id_compte = $_SESSION['id_compte'];

try {
    // Vérification si l'utilisateur a déjà réagi à ce commentaire
    $check_stmt = $pdo->prepare("SELECT * FROM reaction_comment WHERE id_comment = ? AND id_compte = ?");
    $check_stmt->execute([$id_comment, $id_compte]);
    $reaction = $check_stmt->fetch();

    if ($reaction) {
        // Si l'utilisateur a déjà réagi, mettre à jour la réaction
        $update_stmt = $pdo->prepare("UPDATE reaction_comment SET type = ? WHERE id_comment = ? AND id_compte = ?");
        $update_stmt->execute([$type, $id_comment, $id_compte]);
    } else {
        // Sinon, insértion une nouvelle réaction
        $insert_stmt = $pdo->prepare("INSERT INTO reaction_comment (type, id_comment, id_compte) VALUES (?, ?, ?)");
        $insert_stmt->execute([$type, $id_comment, $id_compte]);
    }

    // Redirection vers la publication avec les commentaires
    header("Location: ../comment/show_comments.php?id_publication=" . $id_publication);
    exit();
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}