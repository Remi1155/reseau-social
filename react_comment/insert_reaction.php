<?php
// Connexion à la base de données
require_once '../config/config.php';

if (isset($_GET['type'], $_GET['id_comment'], $_GET['id_compte'])) {
    $type = $_GET['type'];
    $id_comment = (int)$_GET['id_comment'];
    $id_compte = (int)$_GET['id_compte'];

    // Vérifier si l'utilisateur a déjà réagi
    $stmt = $pdo->prepare("SELECT id_reaction FROM reaction_comment WHERE id_comment = ? AND id_compte = ?");
    $stmt->execute([$id_comment, $id_compte]);
    $existingReaction = $stmt->fetch();

    if ($existingReaction) {
        // Mettre à jour la réaction existante
        $stmt = $pdo->prepare("UPDATE reaction_comment SET type = ? WHERE id_reaction = ?");
        $stmt->execute([$type, $existingReaction['id_reaction']]);
    } else {
        // Ajouter une nouvelle réaction
        $stmt = $pdo->prepare("INSERT INTO reaction_comment (id_comment, id_compte, type) VALUES (?, ?, ?)");
        $stmt->execute([$id_comment, $id_compte, $type]);
    }


    // Compter le nombre total de réactions pour cette publication
    $stmt = $pdo->prepare("SELECT COUNT(*) as totalReactions FROM reaction_comment WHERE id_comment = ?");
    $stmt->execute([$id_comment]);
    $totalReactions = $stmt->fetchColumn();

    // Retourner la réaction actuelle comme réponse JSON
    echo json_encode(['reaction' => $type, 'totalReactions' => $totalReactions]);
}
