<?php
session_start();
if (!isset($_SESSION['id_compte'])) {
    header('Location: ../index.php');
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

// Récupération des publications avec le nombre de réactions
$publications = $pdo->query("
    SELECT p.id_publication, p.contenu, p.date_heure, p.id_compte, c.nom, c.prenom,
           IFNULL(COUNT(r.id_reaction), 0) as reactions_count, 
           (SELECT COUNT(*) FROM comments WHERE id_publication = p.id_publication) as comments_count
    FROM publication p
    JOIN compte c ON p.id_compte = c.id
    LEFT JOIN reaction_publication r ON p.id_publication = r.id_publication
    GROUP BY p.id_publication
    ORDER BY p.date_heure DESC
");


// Récupération de tous les utilisateurs
$users = $pdo->query("SELECT nom, prenom FROM compte");

?>


<!DOCTYPE html>
<html>

<head>
    <title>Accueil</title>
    <link rel="stylesheet" href="../styles/output.css">
</head>

<body class="text-xl ">
    <div id="container" class="h-full">
        <header class="w-full h-[100px] bg-blue-500 flex items-center justify-between px-12 text-3xl fixed overflow-y-hidden">
            <div>Bienvenue, <?php echo $_SESSION['prenom'] . ' ' . $_SESSION['nom']; ?></div>
            <a href="./logout.php" class="mt-4 inline-block bg-gray-400 hover:bg-gray-500 text-white font-bold py-2 px-4 text-sm rounded transition duration-300 ease-in-out transform hover:-translate-y-0.5">
                Déconnecter
            </a>
        </header>

        <main class="w-full flex px-12 bg-gray-200 pt-8  pt-[120px]">
            <!-- Partie gauche -->
            <div class=" w-1/5 min-h-screen bg-gray-50 border border-gray-200 shadow-lg rounded-lg p-4 ">
                <h2 class="text-2xl font-extrabold text-gray-800 mb-4">Liste des amis:</h2>
                <ul class="list-disc list-inside pl-5 space-y-3">
                    <?php foreach ($users as $user):
                        if ($user['nom'] != $_SESSION['nom']): ?>
                            <li class="text-lg text-gray-700 font-medium">
                                <?php echo htmlspecialchars($user['prenom']) . ' ' . htmlspecialchars($user['nom']); ?>
                            </li>
                    <?php endif;
                    endforeach; ?>
                </ul>
            </div>

            <!-- Partie principale -->
            <div class="w-3/5 mx-4 bg-gray-300 rounded-lg p-4 overflow-y-auto overflow-y-scroll">
                <div class="items-center bg-gray-300 w-full">
                    <div class="bg-gray-300 text-sm mb-4">A quoi pensez-vous ?</div>

                    <!-- Formulaire de publication -->
                    <form method="post" action="" class=" flex items-center w-full mx-auto">
                        <textarea
                            name="contenu"
                            placeholder="Quoi de neuf ?"
                            required
                            class="w-full border rounded-lg  mr-4 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none h-20">
                        </textarea>
                        <button
                            type="submit"
                            name="publication"
                            class="bg-blue-500 hover:bg-blue-600 text-white font-bold px-8 py-2 rounded text-xs ">
                            Publier
                        </button>
                    </form>
                </div>


                <div class="w-full">
                    <!-- Liste des publications -->
                    <div class="bg-gray-300 text-sm mt-12 mb-4">Publications récentes:</div>
                    <?php
                    foreach ($publications as $publication) {

                        // Vérifier si l'utilisateur a déjà réagi à cette publication
                        $stmt = $pdo->prepare("SELECT type FROM reaction_publication WHERE id_publication = ? AND id_compte = ?");
                        $stmt->execute([$publication['id_publication'], $_SESSION['id_compte']]);
                        $userReaction = $stmt->fetchColumn();
                    ?>

                        <div class=" mb-8 p-4 bg-white rounded-lg">
                            <!-- Utilisateur qui publie -->
                            <div class="text-3xl flex items-center">
                                <img src="../img/personeAnonyme2.png" alt="Image de l'Utilisateur" class="h-16 w-16">
                                <?php echo htmlspecialchars($publication['prenom'] . ' ' . $publication['nom']); ?>
                            </div>
                            <!-- Date de publication -->
                            <div class="text-xs text-gray-500"><?php echo htmlspecialchars($publication['date_heure']); ?></div>

                            <div class="bg-gray-400 w-full p-4 pb-0 mb-4 rounded-lg">
                                <div class="flex justify-between">
                                    <!-- Contenu de la publication -->
                                    <div class="mb-6"><?php echo nl2br(htmlspecialchars($publication['contenu'])); ?></div>

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
                                <div class="flex justify-around border-t border-1 border-solid border-gray-600 py-1">
                                    <div class="bg-gray-300 flex w-fit px-4 rounded">
                                        
                                        <!-- Nombre de reactions -->
                                        <div class="mr-4"><span id="reactions-count-<?php echo $publication['id_publication']; ?>"><?php echo $publication['reactions_count']; ?></span></div>

                                        <!-- Boutons de réactions -->
                                        
                                        <button
                                            id="jaime-<?php echo $publication['id_publication']; ?>"
                                            <?php if ($userReaction === 'jaime') echo 'class="bg-blue-500"'; ?>
                                            style="margin-left:10px; font-size:13px"
                                            onclick="envoyerReaction('jaime', <?php echo $publication['id_publication']; ?>, <?php echo $_SESSION['id_compte']; ?>)">
                                            J'aime
                                        </button>

                                        <button 
                                            id="jadore-<?php echo $publication['id_publication']; ?>" 
                                            <?php if ($userReaction === 'jadore') echo 'class="bg-blue-500"'; ?>
                                            style="margin-left:10px; font-size:13px"
                                            onclick="envoyerReaction('jadore', <?php echo $publication['id_publication']; ?>, <?php echo $_SESSION['id_compte']; ?>)">
                                            J'adore
                                        </button>
                                        
                                        <button 
                                            id="haha-<?php echo $publication['id_publication']; ?>"
                                            <?php if ($userReaction === 'haha') echo 'class="bg-blue-500"'; ?> 
                                            style="margin-left:10px; font-size:13px"
                                            onclick="envoyerReaction('haha', <?php echo $publication['id_publication']; ?>, <?php echo $_SESSION['id_compte']; ?>)">
                                            Haha
                                        </button>
                                        
                                        <button 
                                            id="triste-<?php echo $publication['id_publication']; ?>"
                                            <?php if ($userReaction === 'triste') echo 'class="bg-blue-500"'; ?> 
                                            style="margin-left:10px; font-size:13px"
                                            onclick="envoyerReaction('triste', <?php echo $publication['id_publication']; ?>, <?php echo $_SESSION['id_compte']; ?>)">
                                            Triste
                                        </button>
                                    </div>

                                    <!-- Liste des commentaires -->
                                    <div class="bg-gray-300 flex ml-8 px-4 rounded">
                                        <!-- Nombre de commentaires -->
                                        <div class="mr-4"><?php echo $publication['comments_count']; ?></div>
                                        <!-- Lien vers toutes les commentaires -->
                                        <a href="../comment/show_comments.php?id_publication=<?php echo $publication['id_publication']; ?>" class="">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24">
                                                <path d="M8.2881437,19.1950792 C8.38869181,19.1783212 8.49195996,19.1926955 8.58410926,19.2362761 C9.64260561,19.7368747 10.8021412,20 12,20 C16.418278,20 20,16.418278 20,12 C20,7.581722 16.418278,4 12,4 C7.581722,4 4,7.581722 4,12 C4,13.7069096 4.53528582,15.3318588 5.51454846,16.6849571 C5.62010923,16.830816 5.63909672,17.022166 5.5642591,17.1859256 L4.34581002,19.8521348 L8.2881437,19.1950792 Z M3.58219949,20.993197 C3.18698783,21.0590656 2.87870208,20.6565881 3.04523765,20.2921751 L4.53592782,17.0302482 C3.54143337,15.5576047 3,13.818993 3,12 C3,7.02943725 7.02943725,3 12,3 C16.9705627,3 21,7.02943725 21,12 C21,16.9705627 16.9705627,21 12,21 C10.707529,21 9.4528641,20.727055 8.30053434,20.2068078 L3.58219949,20.993197 Z" />
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Champ pour commenter -->
                            <form method="post" action="../comment/comment.php" class="w-full">
                                <input name="commentaire" placeholder="Ajouter un commentaire" class="text-base border border-1 border-solid border-gray-600 p-2 rounded-md w-4/5"></input>
                                <input type="hidden" name="id_publication" value="<?php echo $publication['id_publication']; ?>">
                                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white text-xs font-bold  px-4 py-2 rounded text-sm ml-2">Commenter</button>
                            </form>

                        </div>
                    <?php } ?>
                </div>
            </div>

            <!-- Partie droite -->
            <div class="w-1/5 min-h-screen bg-white rounded-lg p-4">
                Menu
            </div>
        </main>
    </div>

    <script src="../scripts/reaction_pub.js"></script>


</body>

</html>