<?php
session_start();
if (!isset($_SESSION['id_compte'])) {
    header('Location: ../index.php');
    exit();
}

// Connexion à la base de données
require_once "../config/config.php";

// Ajouter une réaction à une publication
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_publication = $_POST['id_publication'];
    $reaction = $_POST['reaction'];
    
    // Vérifier si l'utilisateur a déjà réagi à cette publication
    $stmt = $pdo->prepare("SELECT id_reaction FROM reaction_publication WHERE id_publication = ? AND id_compte = ?");
    $stmt->execute([$id_publication, $_SESSION['id_compte']]);
    $existingReaction = $stmt->fetch();

    if ($existingReaction) {
        // Mettre à jour la réaction existante
        $stmt = $pdo->prepare("UPDATE reaction_publication SET type = ? WHERE id_reaction = ?");
        $stmt->execute([$reaction, $existingReaction['id_reaction']]);
    } else {
        // Ajouter une nouvelle réaction
        $stmt = $pdo->prepare("INSERT INTO reaction_publication (id_publication, id_compte, type) VALUES (?, ?, ?)");
        $stmt->execute([$id_publication, $_SESSION['id_compte'], $reaction]);
    }

    // Rediriger vers la page d'accueil
    header('Location: ../others/home.php');
    exit();
}
?>
