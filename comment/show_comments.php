<?php
session_start();
if (!isset($_SESSION['id_compte'])) {
    header('Location: ../others/home.php');
    exit();
}

// Connexion à la base de données
require_once '../config/config.php';

// Pour pouvoir utiliser la fonction showUser
require_once '../components/showUser.php';


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
           (SELECT COUNT(*) FROM reaction_comment r WHERE r.id_comment = c.id_comment) AS reactions_count
    FROM comments c
    JOIN compte a ON c.id_compte = a.id
    WHERE c.id_publication = ?
    ORDER BY c.date_heure DESC
");
$comments_stmt->execute([$id_publication]);
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

<body class="bg-[#004DF211] text-gray-900 w-screen h-screen flex items-center justify-center">

    <!-- Conteneur principal -->
    <div class="w-1/2 h-3/4 overflow-hidden mx-auto p-6 bg-[#1166FB18] overflow-y-scroll rounded-lg relative">

        <!-- Bouton de retour -->
        <div class="fixed top-1/6 right-1/4">
            <a href="../others/home.php#<?php echo $id_publication ?>">
                <button class="bg-gray-400 text-white px-4 py-2 rounded-full hover:bg-gray-500 transition duration-300 border border-[#0F89FD7F] border-solid border-2 mx-4">
                    X
                </button>
            </a>
        </div>

        <!-- Affichage de la publication -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <!-- Utilisateur qui publie -->
            <?php echo showUser("../img/personeAnonyme2.png", $publication['prenom'], $publication['nom']) ?>

            <p class="text-black mb-4 text-2xl"><?php echo nl2br(htmlspecialchars($publication['contenu'])); ?></p>
            <p class="text-xs text-gray-500">Publié le : <?php echo htmlspecialchars($publication['date_heure']); ?></p>
        </div>

        <!-- Section des commentaires -->
        <h2 class="text-xl font-semibold mb-4">Commentaires</h2>

        <!-- Affichage des commentaires -->
        <?php foreach ($commentaires as $commentaire) {

            // Vérifier si l'utilisateur a déjà réagi à cette publication
            $stmt = $pdo->prepare("SELECT type FROM reaction_comment WHERE id_comment = ? AND id_compte = ?");
            $stmt->execute([$commentaire['id_comment'], $_SESSION['id_compte']]);
            $userReaction = $stmt->fetchColumn();

        ?>
            <div class="bg-white p-4 rounded-lg shadow-md mb-4">
                <!-- <?php echo showUser("../img/personeAnonyme2.png", $commentaire['prenom'], $commentaire['nom']) ?> -->
                <p class="text-2xl"><?php echo htmlspecialchars($commentaire['prenom'] . ' ' . $commentaire['nom']) ?></p>

                <p class="text-gray-700 text-xl"><?php echo nl2br(htmlspecialchars($commentaire['contenu'])); ?></p>
                <p class="text-xs text-gray-500 mt-4">Publié le : <?php echo htmlspecialchars($commentaire['date_heure']); ?></p>


                <div class="flex">
                    <!-- Nombre de reactions -->
                    <div id="reaction-count-<?php echo $commentaire['id_comment']; ?>">
                        <?php echo $commentaire['reactions_count']; ?>
                    </div>

                    <!-- Boutons de réactions -->

                    <button
                        id="jaime-<?php echo $commentaire['id_comment']; ?>"
                        <?php if ($userReaction === 'jaime') echo 'class="bg-blue-500"'; ?>
                        style="margin-left:10px; font-size:13px"
                        onclick="envoyerReaction('jaime', <?php echo $commentaire['id_comment']; ?>, <?php echo $_SESSION['id_compte']; ?>)">
                        J'aime
                    </button>

                    <button
                        id="jadore-<?php echo $commentaire['id_comment']; ?>"
                        <?php if ($userReaction === 'jadore') echo 'class="bg-blue-500"'; ?>
                        style="margin-left:10px; font-size:13px"
                        onclick="envoyerReaction('jadore', <?php echo $commentaire['id_comment']; ?>, <?php echo $_SESSION['id_compte']; ?>)">
                        J'adore
                    </button>

                    <button
                        id="haha-<?php echo $commentaire['id_comment']; ?>"
                        <?php if ($userReaction === 'haha') echo 'class="bg-blue-500"'; ?>
                        style="margin-left:10px; font-size:13px"
                        onclick="envoyerReaction('haha', <?php echo $commentaire['id_comment']; ?>, <?php echo $_SESSION['id_compte']; ?>)">
                        Haha
                    </button>

                    <button
                        id="triste-<?php echo $commentaire['id_comment']; ?>"
                        <?php if ($userReaction === 'triste') echo 'class="bg-blue-500"'; ?>
                        style="margin-left:10px; font-size:13px"
                        onclick="envoyerReaction('triste', <?php echo $commentaire['id_comment']; ?>, <?php echo $_SESSION['id_compte']; ?>)">
                        Triste
                    </button>
                </div>


            </div>
        <?php } ?>


        <!-- Champ pour commenter -->
        <form method="post" action="../comment/comment.php" class="w-full">
            <input name="commentaire" placeholder="Ajouter un commentaire" class="text-base border border-1 border-solid border-gray-600 p-2 rounded-md w-4/5"></input>
            <input type="hidden" name="id_publication" value="<?php echo $publication['id_publication']; ?>">
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white text-xs font-bold  px-4 py-2 rounded text-sm ml-2">Commenter</button>
        </form>


    </div>

    <script src="../scripts/reaction_comment.js"></script>

</body>

</html>
