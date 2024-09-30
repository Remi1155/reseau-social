<?php
session_start();
if (!isset($_SESSION['id_compte'])) {
    header('Location: ../others/home.php');
    exit();
}

// Connexion à la base de données
require_once '../config/config.php';

// Vérification si l'ID de la publication est passé en paramètre
if (!isset($_GET['id_publication'])) {
    echo "Aucune publication sélectionnée.";
    exit();
}

$id_publication = $_GET['id_publication'];

// Récupération des détails de la publication
$stmt = $pdo->prepare("
    SELECT p.id_publication, p.contenu, p.date_heure, p.id_compte, c.nom, c.prenom
    FROM publication p
    JOIN compte c ON p.id_compte = c.id
    WHERE p.id_publication = ?
");
$stmt->execute([$id_publication]);
$publication = $stmt->fetch();

// Récupération des commentaires de la publication
$comments_stmt = $pdo->prepare("
    SELECT c.contenu, c.date_heure, c.id_compte, c.id_comment, a.nom, a.prenom,
           (SELECT COUNT(*) FROM reaction_comment r WHERE r.id_comment = c.id_comment AND r.type = 'j\'aime') AS jaime,
           (SELECT COUNT(*) FROM reaction_comment r WHERE r.id_comment = c.id_comment AND r.type = 'j\'adore') AS jadore,
           (SELECT COUNT(*) FROM reaction_comment r WHERE r.id_comment = c.id_comment AND r.type = 'haha') AS haha,
           (SELECT COUNT(*) FROM reaction_comment r WHERE r.id_comment = c.id_comment AND r.type = 'triste') AS triste,
           (SELECT r.type FROM reaction_comment r WHERE r.id_comment = c.id_comment AND r.id_compte = ?) AS user_reaction
    FROM comments c
    JOIN compte a ON c.id_compte = a.id
    WHERE c.id_publication = ?
    ORDER BY c.date_heure DESC
");
$comments_stmt->execute([$_SESSION['id_compte'], $id_publication]);
$commentaires = $comments_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publication</title>
    <link rel="stylesheet" href="../styles/output.css">
</head>

<body class="bg-gray-100 text-gray-900">

    <!-- Conteneur principal -->
    <div class="max-w-4xl mx-auto p-6">

        <!-- Titre de la page -->
        <h1 class="text-3xl font-bold mb-6 text-center">Publication</h1>

        <!-- Bouton de retour -->
        <div class="mb-6">
            <a href="../others/home.php">
                <button class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-300">
                    Retour
                </button>
            </a>
        </div>

        <!-- Affichage de la publication -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-2xl font-semibold mb-2"><?php echo htmlspecialchars($publication['prenom'] . ' ' . $publication['nom']); ?></h2>
            <p class="text-gray-700 mb-4"><?php echo nl2br(htmlspecialchars($publication['contenu'])); ?></p>
            <p class="text-sm text-gray-500">Publié le : <?php echo htmlspecialchars($publication['date_heure']); ?></p>
        </div>

        <!-- Section des commentaires -->
        <h2 class="text-xl font-semibold mb-4">Commentaires</h2>

        <!-- Affichage des commentaires -->
        <?php foreach ($commentaires as $commentaire) { ?>
            <div class="bg-white p-4 rounded-lg shadow-md mb-4">
                <p class="text-lg font-semibold"><?php echo htmlspecialchars($commentaire['prenom'] . ' ' . $commentaire['nom']); ?></p>
                <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($commentaire['contenu'])); ?></p>
                <p class="text-sm text-gray-500">Publié le : <?php echo htmlspecialchars($commentaire['date_heure']); ?></p>

                <!-- Affichage des réactions -->
                <div class="mt-2 text-sm">
                    <span class="mr-2">J'aime: <?php echo $commentaire['jaime']; ?></span> |
                    <span class="mx-2">J'adore: <?php echo $commentaire['jadore']; ?></span> |
                    <span class="mx-2">Haha: <?php echo $commentaire['haha']; ?></span> |
                    <span class="mx-2">Triste: <?php echo $commentaire['triste']; ?></span>
                </div>

                <!-- Formulaire de réaction avec boutons radio -->
                <form method="post" action="../react_comment/reaction_comment.php" id="reactionForm-<?php echo $commentaire['id_comment']; ?>" class="mt-4">
                    <input type="hidden" name="id_comment" value="<?php echo $commentaire['id_comment']; ?>">
                    <input type="hidden" name="id_publication" value="<?php echo $id_publication; ?>">

                    <label for="reaction" class="block mb-1 text-gray-600">Réagir :</label>

                    <!-- Boutons radio avec sélection automatique -->
                    <div class="flex items-center space-x-4">
                        <label class="inline-flex items-center">
                            <input type="radio" name="type" value="j'aime" <?php if ($commentaire['user_reaction'] == "j'aime") echo 'checked'; ?>
                            onchange="submitForm(<?php echo $commentaire['id_comment']; ?>)">
                            <span class="ml-2">J'aime</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="type" value="j'adore" <?php if ($commentaire['user_reaction'] == "j'adore") echo 'checked'; ?>
                            onchange="submitForm(<?php echo $commentaire['id_comment']; ?>)">
                            <span class="ml-2">J'adore</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="type" value="haha" <?php if ($commentaire['user_reaction'] == "haha") echo 'checked'; ?>
                            onchange="submitForm(<?php echo $commentaire['id_comment']; ?>)">
                            <span class="ml-2">Haha</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="type" value="triste" <?php if ($commentaire['user_reaction'] == "triste") echo 'checked'; ?>
                            onchange="submitForm(<?php echo $commentaire['id_comment']; ?>)">
                            <span class="ml-2">Triste</span>
                        </label>
                    </div>
                </form>
            </div>
        <?php } ?>

        <!-- Formulaire pour ajouter un commentaire -->
        <form method="post" action="../comment/comment.php" class="bg-white p-6 rounded-lg shadow-md mt-8">
            <input type="hidden" name="id_publication" value="<?php echo $publication['id_publication']; ?>">
            <textarea name="commentaire" placeholder="Ajouter un commentaire" class="w-full p-2 border border-gray-300 rounded mb-4"></textarea>
            <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition duration-300">
                Commenter
            </button>
        </form>

    </div>

    <script>
        // Fonction pour soumettre automatiquement le formulaire lorsqu'une réaction est choisie
        function submitForm(commentId) {
            document.getElementById('reactionForm-' + commentId).submit();
        }
    </script>

</body>

</html>
