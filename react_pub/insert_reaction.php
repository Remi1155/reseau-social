<?php
// Connexion à la base de données
require_once '../config/config.php';

if (isset($_GET['type'], $_GET['id_publication'], $_GET['id_compte'])) {
    $type = $_GET['type'];
    $id_publication = (int)$_GET['id_publication'];
    $id_compte = (int)$_GET['id_compte'];

    // Vérifier si l'utilisateur a déjà réagi
    $stmt = $pdo->prepare("SELECT id_reaction FROM reaction_publication WHERE id_publication = ? AND id_compte = ?");
    $stmt->execute([$id_publication, $id_compte]);
    $existingReaction = $stmt->fetch();

    if ($existingReaction) {
        // Mettre à jour la réaction existante
        $stmt = $pdo->prepare("UPDATE reaction_publication SET type = ? WHERE id_reaction = ?");
        $stmt->execute([$type, $existingReaction['id_reaction']]);
    } else {
        // Ajouter une nouvelle réaction
        $stmt = $pdo->prepare("INSERT INTO reaction_publication (id_publication, id_compte, type) VALUES (?, ?, ?)");
        $stmt->execute([$id_publication, $id_compte, $type]);
    }


    // Récupérer le nombre total de réactions pour cette publication
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM reaction_publication WHERE id_publication = ?");
    $stmt->execute([$idPublication]);
    $nbReactions = $stmt->fetchColumn();

    // Retourner la réaction actuelle comme réponse JSON
    echo json_encode(['reaction' => $type, 'nbReactions' => $nbReactions]);
}
