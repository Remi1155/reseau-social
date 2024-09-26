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
            <div class="bg-gray-50 border border-gray-200 shadow-lg rounded-lg px-8 py-6 m-8 ml-0">
                <h2 class="text-2xl font-extrabold text-gray-800 mb-4">Liste des utilisateurs</h2>
                <ul class="list-disc list-inside pl-5 space-y-3">
                    <?php foreach ($users as $user): ?>
                        <li class="text-lg text-gray-700 font-medium">
                            <?php echo htmlspecialchars($user['prenom']) . ' ' . htmlspecialchars($user['nom']); ?>
                        </li>
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

                                            <label class="inline-flex items-center mr-2.5">
                                                <input type="radio" name="reaction" value="j'aime"
                                                    <?php if ($userReaction == "j'aime") echo 'checked'; ?>
                                                    onchange="submitReaction(<?php echo $publication['id_publication']; ?>)">
                                                <svg version="1.0" xmlns="http://www.w3.org/2000/svg"
                                                    width="16.000000pt" height="16.000000pt" viewBox="0 0 1222.000000 1280.000000"
                                                    preserveAspectRatio="xMidYMid meet">
                                                    <metadata>
                                                        Created by potrace 1.15, written by Peter Selinger 2001-2017
                                                    </metadata>
                                                    <g transform="translate(0.000000,1280.000000) scale(0.100000,-0.100000)"
                                                        fill="#03a9f4" stroke="none">
                                                        <path d="M7258 12774 c-60 -19 -84 -33 -112 -64 -42 -47 -52 -83 -61 -215 -18
                                                            -291 -98 -951 -150 -1240 -83 -456 -247 -909 -432 -1190 -133 -201 -233 -303
                                                            -568 -576 -331 -269 -493 -448 -600 -664 -30 -60 -115 -254 -188 -430 -257
                                                            -610 -395 -906 -578 -1235 -404 -727 -746 -1118 -1086 -1242 -49 -18 -85 -21
                                                            -227 -24 -93 -2 -173 -7 -177 -11 -12 -12 -12 -5224 -1 -5235 5 -5 27 -13 48
                                                            -18 45 -10 716 -135 1129 -210 273 -50 946 -176 1312 -246 101 -19 242 -39
                                                            315 -44 73 -6 207 -17 298 -25 486 -43 917 -65 1910 -96 766 -24 1478 9 1851
                                                            85 274 57 469 154 729 363 180 145 414 400 478 522 63 119 64 127 66 466 1
                                                            290 2 313 22 361 39 95 98 168 305 374 271 270 347 373 385 520 35 137 13 235
                                                            -134 600 -89 221 -112 303 -112 400 0 124 41 189 315 505 188 215 237 315 217
                                                            433 -14 85 -60 177 -209 425 -192 319 -223 400 -199 517 15 72 49 128 208 343
                                                            71 95 145 206 165 247 36 71 38 80 38 170 -1 85 -5 108 -42 216 -51 148 -119
                                                            286 -210 424 -159 239 -329 429 -465 518 -174 113 -368 154 -883 187 -515 33
                                                            -1594 45 -2222 25 -467 -15 -452 -15 -475 8 -46 46 -62 256 -33 428 51 296
                                                            167 579 558 1361 209 416 250 524 327 847 60 254 64 294 64 636 1 339 -3 384
                                                            -56 600 -87 361 -195 573 -396 776 -140 141 -304 254 -468 321 -73 30 -143 49
                                                            -351 98 -94 23 -214 18 -305 -11z" />
                                                        <path d="M435 6135 c-94 -18 -145 -37 -210 -81 -94 -62 -179 -178 -200 -271
                                                            -3 -18 -11 -35 -16 -38 -12 -8 -12 -5078 0 -5090 5 -6 16 -35 25 -66 41 -143
                                                            202 -284 364 -318 37 -8 310 -11 880 -11 911 0 896 -1 1016 63 139 73 234 211
                                                            259 377 3 19 7 1146 9 2505 2 2373 2 2473 -16 2540 -51 194 -178 328 -360 379
                                                            -68 20 -104 20 -876 23 -638 1 -820 -1 -875 -12z" />
                                                    </g>
                                                </svg>
                                            </label>

                                            <label class="inline-flex items-center mr-2.5">
                                                <input type="radio" name="reaction" value="j'adore"
                                                    <?php if ($userReaction == "j'adore") echo 'checked'; ?>
                                                    onchange="submitReaction(<?php echo $publication['id_publication']; ?>)">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" id="love">
                                                    <path fill="url(#a)" d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0Z"></path>
                                                    <path fill="#fff" d="M10.473 4C8.275 4 8 5.824 8 5.824S7.726 4 5.528 4c-2.114 0-2.73 2.222-2.472 3.41C3.736 10.55 8 12.75 8 12.75s4.265-2.2 4.945-5.34c.257-1.188-.36-3.41-2.472-3.41Z"></path>
                                                    <defs>
                                                        <linearGradient id="a" x1="8" x2="8" y2="16" gradientUnits="userSpaceOnUse">
                                                            <stop stop-color="#FF6680"></stop>
                                                            <stop offset="1" stop-color="#E61739"></stop>
                                                        </linearGradient>
                                                    </defs>
                                                </svg>
                                            </label>

                                            <label class="inline-flex items-center mr-2.5">
                                                <input type="radio" name="reaction" value="haha"
                                                    <?php if ($userReaction == "haha") echo 'checked'; ?>
                                                    onchange="submitReaction(<?php echo $publication['id_publication']; ?>)">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" id="haha">
                                                    <path fill="url(#a)" d="M16 8A8 8 0 1 1-.001 8 8 8 0 0 1 16 8"></path>
                                                    <path fill="url(#b)" d="M3 8.008C3 10.023 4.006 14 8 14c3.993 0 5-3.977 5-5.992C13 7.849 11.39 7 8 7c-3.39 0-5 .849-5 1.008Z"></path>
                                                    <path fill="url(#c)" d="M4.541 12.5c.804.995 1.907 1.5 3.469 1.5 1.563 0 2.655-.505 3.459-1.5-.551-.588-1.599-1.5-3.459-1.5s-2.917.912-3.469 1.5Z"></path>
                                                    <path fill="#2A3755" d="M6.213 4.144c.263.188.502.455.41.788-.071.254-.194.369-.422.37-.78.012-1.708.256-2.506.613-.065.029-.197.088-.332.085-.124-.003-.251-.058-.327-.237-.067-.157-.073-.388.276-.598.545-.33 1.257-.48 1.909-.604-.41-.303-.85-.56-1.315-.768-.427-.194-.38-.457-.323-.6.127-.317.609-.196 1.078.026a9 9 0 0 1 1.552.925Zm3.577 0a8.955 8.955 0 0 1 1.55-.925c.47-.222.95-.343 1.078-.026.057.143.104.406-.323.6a7.028 7.028 0 0 0-1.313.768c.65.123 1.363.274 1.907.604.349.21.342.44.276.598-.077.18-.203.234-.327.237-.135.003-.267-.056-.332-.085-.797-.357-1.725-.6-2.504-.612-.228-.002-.351-.117-.422-.37-.091-.333.147-.6.41-.788v-.001Z"></path>
                                                    <defs>
                                                        <linearGradient id="a" x1="8" x2="8" y1="1.64" y2="16" gradientUnits="userSpaceOnUse">
                                                            <stop stop-color="#FEEA70"></stop>
                                                            <stop offset="1" stop-color="#F69B30"></stop>
                                                        </linearGradient>
                                                        <linearGradient id="b" x1="8" x2="8" y1="7" y2="14" gradientUnits="userSpaceOnUse">
                                                            <stop stop-color="#472315"></stop>
                                                            <stop offset="1" stop-color="#8B3A0E"></stop>
                                                        </linearGradient>
                                                        <linearGradient id="c" x1="8.005" x2="8.005" y1="11" y2="13.457" gradientUnits="userSpaceOnUse">
                                                            <stop stop-color="#FC607C"></stop>
                                                            <stop offset="1" stop-color="#D91F3A"></stop>
                                                        </linearGradient>
                                                    </defs>
                                                </svg>
                                            </label>

                                            <label class="inline-flex items-center mr-2.5">
                                                <input type="radio" name="reaction" value="triste"
                                                    <?php if ($userReaction == "triste") echo 'checked'; ?>
                                                    onchange="submitReaction(<?php echo $publication['id_publication']; ?>)">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" id="sad">
                                                    <path fill="url(#a)" d="M16 8A8 8 0 1 1-.001 8 8 8 0 0 1 16 8"></path>
                                                    <path fill="url(#b)" d="M5.333 12.765c0 .137.094.235.25.235.351 0 .836-.625 2.417-.625s2.067.625 2.417.625c.156 0 .25-.098.25-.235C10.667 12.368 9.828 11 8 11c-1.828 0-2.667 1.368-2.667 1.765Z"></path>
                                                    <path fill="url(#c)" d="M3.599 8.8c0-.81.509-1.466 1.134-1.466.627 0 1.134.656 1.134 1.466 0 .338-.09.65-.238.898a.492.492 0 0 1-.301.225c-.14.037-.353.077-.595.077-.243 0-.453-.04-.595-.077a.49.49 0 0 1-.3-.225 1.741 1.741 0 0 1-.24-.898Zm6.534 0c0-.81.508-1.466 1.133-1.466.627 0 1.134.656 1.134 1.466 0 .338-.09.65-.238.898a.49.49 0 0 1-.301.225c-.39.101-.8.101-1.19 0a.49.49 0 0 1-.3-.225 1.74 1.74 0 0 1-.238-.898Z"></path>
                                                    <path fill="#000" d="M3.599 8.8c0-.81.509-1.466 1.134-1.466.627 0 1.134.656 1.134 1.466 0 .338-.09.65-.238.898a.492.492 0 0 1-.301.225c-.14.037-.353.077-.595.077-.243 0-.453-.04-.595-.077a.49.49 0 0 1-.3-.225 1.741 1.741 0 0 1-.24-.898Zm6.534 0c0-.81.508-1.466 1.133-1.466.627 0 1.134.656 1.134 1.466 0 .338-.09.65-.238.898a.49.49 0 0 1-.301.225c-.39.101-.8.101-1.19 0a.49.49 0 0 1-.3-.225 1.74 1.74 0 0 1-.238-.898Z" filter="url(#d)"></path>
                                                    <path fill="#4E506A" d="M4.616 7.986c.128.125.136.372.017.55-.12.179-.32.223-.448.097-.128-.125-.135-.372-.017-.55.12-.18.32-.222.448-.097Zm6.489 0c.128.125.136.372.018.55-.12.179-.32.223-.45.097-.127-.125-.134-.372-.015-.55.119-.18.319-.222.447-.097Z"></path>
                                                    <path fill="url(#e)" d="M4.157 5.153c.332-.153.596-.22.801-.22.277 0 .451.12.55.307.175.329.096.4-.198.459-1.106.224-2.217.942-2.699 1.39-.3.28-.589-.03-.436-.274.154-.244.774-1.105 1.982-1.662Zm6.335.087c.1-.187.273-.306.55-.306.206 0 .47.066.801.219 1.208.557 1.828 1.418 1.981 1.662.153.244-.134.554-.435.274-.483-.448-1.593-1.166-2.7-1.39-.294-.058-.37-.13-.197-.46Z"></path>
                                                    <path fill="url(#f)" d="M13.5 16c-.828 0-1.5-.748-1.5-1.671 0-.922.356-1.545.643-2.147.598-1.258.716-1.432.857-1.432.141 0 .259.174.857 1.432.287.602.643 1.225.643 2.147 0 .923-.672 1.671-1.5 1.671Z"></path>
                                                    <path fill="url(#g)" d="M13.5 13.606c-.328 0-.594-.296-.594-.66 0-.366.141-.613.255-.852.236-.498.283-.566.34-.566.055 0 .102.068.338.566.114.24.255.486.255.851s-.266.661-.594.661"></path>
                                                    <defs>
                                                        <linearGradient id="a" x1="8" x2="8" y1="1.64" y2="16" gradientUnits="userSpaceOnUse">
                                                            <stop stop-color="#FEEA70"></stop>
                                                            <stop offset="1" stop-color="#F69B30"></stop>
                                                        </linearGradient>
                                                        <linearGradient id="b" x1="8" x2="8" y1="11" y2="13" gradientUnits="userSpaceOnUse">
                                                            <stop stop-color="#472315"></stop>
                                                            <stop offset="1" stop-color="#8B3A0E"></stop>
                                                        </linearGradient>
                                                        <linearGradient id="c" x1="7.999" x2="7.999" y1="7.334" y2="10" gradientUnits="userSpaceOnUse">
                                                            <stop stop-color="#191A33"></stop>
                                                            <stop offset=".872" stop-color="#3B426A"></stop>
                                                        </linearGradient>
                                                        <linearGradient id="e" x1="8" x2="8" y1="4.934" y2="7.199" gradientUnits="userSpaceOnUse">
                                                            <stop stop-color="#E78E0D"></stop>
                                                            <stop offset="1" stop-color="#CB6000"></stop>
                                                        </linearGradient>
                                                        <linearGradient id="f" x1="13.5" x2="13.5" y1="15.05" y2="11.692" gradientUnits="userSpaceOnUse">
                                                            <stop stop-color="#35CAFC"></stop>
                                                            <stop offset="1" stop-color="#007EDB"></stop>
                                                        </linearGradient>
                                                        <linearGradient id="g" x1="13.5" x2="13.5" y1="11.528" y2="13.606" gradientUnits="userSpaceOnUse">
                                                            <stop stop-color="#6AE1FF" stop-opacity=".287"></stop>
                                                            <stop offset="1" stop-color="#A8E3FF" stop-opacity=".799"></stop>
                                                        </linearGradient>
                                                        <filter id="d" width="8.801" height="2.666" x="3.599" y="7.334" color-interpolation-filters="sRGB" filterUnits="userSpaceOnUse">
                                                            <feFlood flood-opacity="0" result="BackgroundImageFix"></feFlood>
                                                            <feBlend in="SourceGraphic" in2="BackgroundImageFix" result="shape"></feBlend>
                                                            <feColorMatrix in="SourceAlpha" result="hardAlpha" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"></feColorMatrix>
                                                            <feOffset></feOffset>
                                                            <feGaussianBlur stdDeviation=".5"></feGaussianBlur>
                                                            <feComposite in2="hardAlpha" k2="-1" k3="1" operator="arithmetic"></feComposite>
                                                            <feColorMatrix values="0 0 0 0 0.0411227 0 0 0 0 0.0430885 0 0 0 0 0.0922353 0 0 0 0.819684 0"></feColorMatrix>
                                                            <feBlend in2="shape" result="effect1_innerShadow"></feBlend>
                                                        </filter>
                                                    </defs>
                                                </svg>

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