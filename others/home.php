<?php
session_start();
if (!isset($_SESSION['id_compte'])) {
    header('Location: login.php');
    exit();
}

// Connexion à la base de données
require_once '../config/config.php';

// Création d'une publication
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['publication'])) {
    $contenu = $_POST['contenu'];
    $stmt = $pdo->prepare("INSERT INTO publication (id_compte, contenu) VALUES (?, ?)");
    $stmt->execute([$_SESSION['id_compte'], $contenu]);
}

// Récupérer les publications
// Récupérer les publications avec le nombre de réactions
$publications = $pdo->query("
    SELECT p.id_publication, p.contenu, p.date_heure, p.id_compte, c.nom, c.prenom,
           COUNT(r.id_reaction) as reactions_count, 
           (SELECT COUNT(*) FROM comments WHERE id_publication = p.id_publication) as comments_count
    FROM publication p
    JOIN compte c ON p.id_compte = c.id
    LEFT JOIN reaction_publication r ON p.id_publication = r.id_publication
    GROUP BY p.id_publication
    ORDER BY p.date_heure DESC
");


// Récupérer tous les utilisateurs
$users = $pdo->query("SELECT nom, prenom FROM compte");

?>


<!DOCTYPE html>
<html>

<head>
    <title>Accueil</title>
    <!-- <link rel="stylesheet" href="./styles/home.css"> -->
    <link rel="stylesheet" href="../styles/output.css">
</head>

<body class="text-xl">
    <div id="container">
        <header class="w-full h-[100px] bg-blue-500 flex items-center justify-between px-12 text-3xl">
            <div>Bienvenue, <?php echo $_SESSION['prenom'] . ' ' . $_SESSION['nom']; ?></div>
            <a href="./logout.php" class="mt-4 inline-block bg-gray-400 hover:bg-gray-500 text-white font-bold py-2 px-4 text-sm rounded transition duration-300 ease-in-out transform hover:-translate-y-0.5">
                Déconnecter
            </a>
        </header>

        <main class="flex px-12 bg-gray-100">
            <div class="bg-white px-8 m-8 ml-0">
                <h2 class="text-2xl font-bold mb-4">Liste des utilisateurs</h2>
                <ul class="list-disc pl-5">
                    <?php foreach ($users as $user): ?>
                        <li class="mb-2"><?php echo htmlspecialchars($user['prenom']) . ' ' . htmlspecialchars($user['nom']); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="w-full">
                <div class="items-center bg-gray-100 py-8 w-full">
                    <!-- Formulaire de publication -->
                    <form method="post" action="" class=" flex w-full mx-auto space-y-4">
                        <textarea
                            name="contenu"
                            placeholder="Quoi de neuf ?"
                            required
                            class="w-3/4 border rounded-lg  mr-4 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </textarea>
                        <button
                            type="submit"
                            name="publication"
                            class="bg-blue-500 hover:bg-blue-600 text-white font-bold px-8 rounded text-xs">
                            Publier
                        </button>
                    </form>



                </div>


                <div class="">
                    <!-- Liste des publications -->
                    <div class="">Publications récentes</div>
                    <?php
                    foreach ($publications as $publication) {

                        // Vérifier si l'utilisateur a déjà réagi à cette publication
                        $stmt = $pdo->prepare("SELECT type FROM reaction_publication WHERE id_publication = ? AND id_compte = ?");
                        $stmt->execute([$publication['id_publication'], $_SESSION['id_compte']]);
                        $userReaction = $stmt->fetchColumn();
                    ?>

                        <div class=" my-8 p-4 bg-white">
                            <!-- Utilisateur qui publie -->
                            <div class="text-3xl flex items-center">
                                <img src="../img/personeAnonyme2.png" alt="Image de l'Utilisateur" class="h-16 w-16">
                                <?php echo htmlspecialchars($publication['prenom'] . ' ' . $publication['nom']); ?>
                            </div>
                            <!-- Date de publication -->
                            <div class="text-xs text-gray-500"><?php echo htmlspecialchars($publication['date_heure']); ?></div>

                            <div class="bg-gray-200 w-fit p-4 pb-0 mb-4">
                                <div class="flex justify-between">
                                    <!-- Contenu de la publication -->
                                    <div class="mb-8"><?php echo htmlspecialchars($publication['contenu']); ?></div>

                                    <!-- Suppression de la publication -->
                                    <!-- Si l'utilisateur est l'auteur de la publication, afficher un lien pour la supprimer -->
                                    <?php if ($publication['id_compte'] == $_SESSION['id_compte']) { ?>
                                        <form method="post" action="./delete_publication.php" class="ml-8">
                                            <input type="hidden" name="id_publication" value="<?php echo $publication['id_publication']; ?>">
                                            <button type="submit" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette publication ?');">
                                                <svg fill="#000000" width="24px" height="24px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M22,5H17V2a1,1,0,0,0-1-1H8A1,1,0,0,0,7,2V5H2A1,1,0,0,0,2,7H3.117L5.008,22.124A1,1,0,0,0,6,23H18a1,1,0,0,0,.992-.876L20.883,7H22a1,1,0,0,0,0-2ZM9,3h6V5H9Zm8.117,18H6.883L5.133,7H18.867Z" />
                                                </svg>
                                            </button>
                                        </form>
                                    <?php } ?>
                                </div>

                                <!-- Bouttons de reactions et affichage des commentaires -->
                                <div class="flex">
                                    <div class="bg-gray-400 flex w-fit px-4 rounded-lg">
                                        <!-- Nombre de reactions -->
                                        <div class="mr-4"><?php echo $publication['reactions_count']; ?></div>

                                        <!-- Boutons de réactions -->
                                        <form method="post" action="../react_pub/react.php" id="reactionForm<?php echo $publication['id_publication']; ?>">
                                            <input type="hidden" name="id_publication" value="<?php echo $publication['id_publication']; ?>">

                                            <label>
                                                <input type="radio" name="reaction" value="j'aime"
                                                    <?php if ($userReaction == "j'aime") echo 'checked'; ?>
                                                    onchange="submitReaction(<?php echo $publication['id_publication']; ?>)">
                                                J'aime
                                            </label>
                                            <label>
                                                <input type="radio" name="reaction" value="j'adore"
                                                    <?php if ($userReaction == "j'adore") echo 'checked'; ?>
                                                    onchange="submitReaction(<?php echo $publication['id_publication']; ?>)"> J'adore
                                            </label>
                                            <label>
                                                <input type="radio" name="reaction" value="haha"
                                                    <?php if ($userReaction == "haha") echo 'checked'; ?>
                                                    onchange="submitReaction(<?php echo $publication['id_publication']; ?>)"> Haha
                                            </label>
                                            <label>
                                                <input type="radio" name="reaction" value="triste"
                                                    <?php if ($userReaction == "triste") echo 'checked'; ?>
                                                    onchange="submitReaction(<?php echo $publication['id_publication']; ?>)"> Triste
                                            </label>
                                        </form>
                                    </div>

                                    <!-- Liste des commentaires -->
                                    <div class="bg-gray-400 flex ml-8 px-4 rounded-lg">
                                        <!-- Nombre de commentaires -->
                                        <div class="mr-4"><?php echo $publication['comments_count']; ?></div>
                                        <!-- Lien vers toutes les commentaires -->
                                        <a href="../comment/show_comments.php?id_publication=<?php echo $publication['id_publication']; ?>" class="">
                                            <svg width="25px" height="25px" viewBox="0 0 32 32" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:sketch="http://www.bohemiancoding.com/sketch/ns">
                                                <title>comment-2</title>
                                                <desc>Created with Sketch Beta.</desc>
                                                <defs></defs>
                                                <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd" sketch:type="MSPage">
                                                    <g id="Icon-Set" sketch:type="MSLayerGroup" transform="translate(-152.000000, -255.000000)" fill="#000000">
                                                        <path d="M168,281 C166.832,281 165.704,280.864 164.62,280.633 L159.912,283.463 L159.975,278.824 C156.366,276.654 154,273.066 154,269 C154,262.373 160.268,257 168,257 C175.732,257 182,262.373 182,269 C182,275.628 175.732,281 168,281 L168,281 Z M168,255 C159.164,255 152,261.269 152,269 C152,273.419 154.345,277.354 158,279.919 L158,287 L165.009,282.747 C165.979,282.907 166.977,283 168,283 C176.836,283 184,276.732 184,269 C184,261.269 176.836,255 168,255 L168,255 Z M175,266 L161,266 C160.448,266 160,266.448 160,267 C160,267.553 160.448,268 161,268 L175,268 C175.552,268 176,267.553 176,267 C176,266.448 175.552,266 175,266 L175,266 Z M173,272 L163,272 C162.448,272 162,272.447 162,273 C162,273.553 162.448,274 163,274 L173,274 C173.552,274 174,273.553 174,273 C174,272.447 173.552,272 173,272 L173,272 Z" id="comment-2" sketch:type="MSShapeGroup"></path>
                                                    </g>
                                                </g>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Champ pour commenter -->
                            <form method="post" action="../comment/comment.php">
                                <input name="commentaire" placeholder="Ajouter un commentaire" class="text-base border border-1 border-solid border-gray-600 p-4 rounded-sm"></input>
                                <input type="hidden" name="id_publication" value="<?php echo $publication['id_publication']; ?>">
                                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold  p-4 rounded text-xs">Commenter</button>
                            </form>

                        </div>
                    <?php } ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        function submitReaction(publicationId) {
            document.getElementById('reactionForm' + publicationId).submit();
        }
    </script>


</body>

</html>